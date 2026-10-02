<?php

/**
 * Checks the docs/ folder before it is published to focalcrm.io/docs:
 * every page in navigation.yml exists and has a title and description,
 * every Markdown page is listed, and every relative .md link (and its
 * #anchor) points at a page and heading that exist.
 *
 * Usage: php .github/scripts/check-docs.php [docs-path]
 */
$root = rtrim($argv[1] ?? __DIR__.'/../../docs', '/');
$errors = [];

// navigation.yml is a small, fixed shape: "- title:" sections with "pages:" lists.
$listed = [];
foreach (file($root.'/navigation.yml') as $line) {
    if (preg_match('/^\s{4}- ([a-z0-9\-\/]+)\s*$/', $line, $match)) {
        $listed[] = $match[1];
    }
}

$pages = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() === 'md') {
        $pages[substr($file->getPathname(), strlen($root) + 1, -3)] = file_get_contents($file->getPathname());
    }
}

foreach ($listed as $slug) {
    if (! isset($pages[$slug])) {
        $errors[] = "navigation.yml lists {$slug}, but docs/{$slug}.md doesn't exist.";
    }
}

foreach (array_keys($pages) as $slug) {
    if (! in_array($slug, $listed, true) && $slug !== 'CONTRIBUTING') {
        $errors[] = "docs/{$slug}.md isn't listed in navigation.yml, so it won't be published.";
    }
}

/** GitHub-style heading anchors, matching league/commonmark's slug normalizer. */
$anchors = function (string $markdown): array {
    $markdown = preg_replace('/^```.*?^```/ms', '', $markdown);
    preg_match_all('/^#{2,3}\s+(.+?)\s*$/m', $markdown, $matches);

    return array_map(function (string $heading): string {
        $text = strip_tags(preg_replace('/`([^`]*)`/', '$1', $heading));
        $text = mb_strtolower(trim(preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text)));

        return preg_replace('/\s/', '-', preg_replace('/[^\p{L}\p{M}\p{N}\p{Pc}\s-]/u', '', $text));
    }, $matches[1]);
};

foreach ($pages as $slug => $markdown) {
    if (! preg_match('/\A---\n(.*?)\n---\n/s', $markdown, $frontMatter)) {
        $errors[] = "docs/{$slug}.md has no front matter.";
    } else {
        foreach (['title', 'description'] as $key) {
            if (! preg_match('/^'.$key.':\s*\S/m', $frontMatter[1])) {
                $errors[] = "docs/{$slug}.md front matter has no {$key}.";
            }
        }
    }

    // Ignore links inside code: fenced blocks and inline code spans.
    $prose = preg_replace(['/^```.*?^```/ms', '/`[^`\n]*`/'], '', $markdown);
    preg_match_all('/\]\(([^)\s]+)\)/', $prose, $links);

    foreach ($links[1] as $href) {
        if (preg_match('#^([a-z][a-z0-9+.-]*:|/|\#)#i', $href)) {
            if (str_starts_with($href, '#') && ! in_array(substr($href, 1), $anchors($markdown), true)) {
                $errors[] = "docs/{$slug}.md links to {$href}, which isn't a heading on that page.";
            }

            continue;
        }

        [$path, $fragment] = array_pad(explode('#', $href, 2), 2, null);
        if (! str_ends_with($path, '.md')) {
            continue;
        }

        $target = [];
        foreach (explode('/', (str_contains($slug, '/') ? dirname($slug).'/' : '').substr($path, 0, -3)) as $segment) {
            if ($segment === '..') {
                array_pop($target);
            } elseif ($segment !== '' && $segment !== '.') {
                $target[] = $segment;
            }
        }
        $target = implode('/', $target);

        if (! isset($pages[$target])) {
            $errors[] = "docs/{$slug}.md links to {$href}, but docs/{$target}.md doesn't exist.";
        } elseif ($fragment !== null && ! in_array($fragment, $anchors($pages[$target]), true)) {
            $errors[] = "docs/{$slug}.md links to {$href}, but docs/{$target}.md has no #{$fragment} heading.";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);
}

echo 'Docs OK: '.count($listed).' published pages.'.PHP_EOL;
