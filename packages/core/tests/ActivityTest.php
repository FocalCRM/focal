<?php

declare(strict_types=1);

namespace Focal\Core\Tests;

use Focal\Core\Actions\LogActivityAction;
use Focal\Core\Enums\ActivityStatus;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Events\ActivityLogged;
use Focal\Core\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_log_notes_and_calls_on_contact(): void
    {
        $contact = Contact::factory()->create();

        $note = $contact->logNote('Met at conference, interested in demo.');
        $call = $contact->logCall('Discovery Call', 'Discussed pricing and team size.', ['duration_seconds' => 300]);

        $this->assertSame(ActivityType::Note, $note->type);
        $this->assertSame('Met at conference, interested in demo.', $note->body);

        $this->assertSame(ActivityType::Call, $call->type);
        $this->assertSame(300, $call->metadata['duration_seconds']);

        $activities = $contact->activities;
        $this->assertCount(2, $activities);
    }

    public function test_can_log_task_with_due_date(): void
    {
        $contact = Contact::factory()->create();
        $dueDate = now()->addDays(3);

        $task = $contact->logTask('Follow up on proposal', $dueDate);

        $this->assertSame(ActivityType::Task, $task->type);
        $this->assertSame(ActivityStatus::Pending, $task->status);
        $this->assertNotNull($task->due_at);
        $this->assertNull($task->completed_at);
    }

    public function test_log_activity_action_dispatches_event(): void
    {
        Event::fake([ActivityLogged::class]);

        $contact = Contact::factory()->create();

        $action = new LogActivityAction;
        $action->execute(
            subject: $contact,
            type: ActivityType::Meeting,
            title: 'Q3 Business Review',
            body: 'Reviewed pipeline forecast.'
        );

        Event::assertDispatched(ActivityLogged::class);
    }
}
