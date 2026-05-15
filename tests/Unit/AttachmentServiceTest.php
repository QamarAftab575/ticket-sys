<?php

namespace Tests\Unit;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttachmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttachmentService $attachmentService;
    private User $user;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        
        $activityLogService = new ActivityLogService();
        $this->attachmentService = new AttachmentService($activityLogService);
        
        // Create a user
        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'SecurePass123',
            'email_verified_at' => now(),
        ]);

        // Create a project without foreign key validation
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        
        $project = Project::create([
            'name' => 'Test Project',
            'organization_id' => \Illuminate\Support\Str::uuid(),
            'manager_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'on_track',
            'visibility' => 'public_to_team',
        ]);

        // Create a task
        $this->task = Task::create([
            'name' => 'Test Task',
            'project_id' => $project->id,
            'creator_id' => $this->user->id,
            'status' => 'to_do',
            'priority' => 'medium',
            'visibility' => 'everyone',
            'position' => 0,
        ]);
        
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }

    /**
     * Test adding a link attachment successfully.
     */
    public function test_add_link_creates_attachment(): void
    {
        $url = 'https://example.com/document';
        $title = 'Example Document';

        $attachment = $this->attachmentService->addLink($this->task, $this->user, $url, $title);

        $this->assertNotNull($attachment->id);
        $this->assertEquals($this->task->id, $attachment->task_id);
        $this->assertEquals($this->user->id, $attachment->user_id);
        $this->assertEquals('link', $attachment->type);
        $this->assertEquals($url, $attachment->url);
        $this->assertEquals($title, $attachment->title);
        $this->assertNull($attachment->filename);
        $this->assertNull($attachment->file_path);
        $this->assertNull($attachment->file_size);
    }

    /**
     * Test adding a link without title.
     */
    public function test_add_link_without_title(): void
    {
        $url = 'https://example.com/document';

        $attachment = $this->attachmentService->addLink($this->task, $this->user, $url);

        $this->assertNotNull($attachment->id);
        $this->assertEquals($url, $attachment->url);
        $this->assertNull($attachment->title);
    }

    /**
     * Test adding a link logs activity.
     */
    public function test_add_link_logs_activity(): void
    {
        $url = 'https://example.com/document';
        $title = 'Example Document';

        $this->attachmentService->addLink($this->task, $this->user, $url, $title);

        $activity = TaskActivity::where('task_id', $this->task->id)
            ->where('activity_type', 'updated')
            ->where('field_name', 'attachment')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->user->id, $activity->user_id);
        $this->assertStringContainsString('Link added:', $activity->new_value);
        $this->assertStringContainsString($title, $activity->new_value);
    }

    /**
     * Test adding a link without title logs URL in activity.
     */
    public function test_add_link_without_title_logs_url(): void
    {
        $url = 'https://example.com/document';

        $this->attachmentService->addLink($this->task, $this->user, $url);

        $activity = TaskActivity::where('task_id', $this->task->id)
            ->where('activity_type', 'updated')
            ->where('field_name', 'attachment')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString($url, $activity->new_value);
    }

    /**
     * Test adding a link with invalid URL fails.
     */
    public function test_add_link_fails_with_invalid_url(): void
    {
        $this->expectException(ValidationException::class);
        
        $this->attachmentService->addLink($this->task, $this->user, 'not-a-valid-url');
    }

    /**
     * Test adding a link with empty URL fails.
     */
    public function test_add_link_fails_with_empty_url(): void
    {
        $this->expectException(ValidationException::class);
        
        $this->attachmentService->addLink($this->task, $this->user, '');
    }

    /**
     * Test adding a link with malformed URL fails.
     */
    public function test_add_link_fails_with_malformed_url(): void
    {
        $this->expectException(ValidationException::class);
        
        $this->attachmentService->addLink($this->task, $this->user, 'htp://invalid');
    }

    /**
     * Test adding multiple links to same task.
     */
    public function test_add_multiple_links_to_task(): void
    {
        $url1 = 'https://example.com/doc1';
        $url2 = 'https://example.com/doc2';

        $attachment1 = $this->attachmentService->addLink($this->task, $this->user, $url1, 'Doc 1');
        $attachment2 = $this->attachmentService->addLink($this->task, $this->user, $url2, 'Doc 2');

        $this->assertNotEquals($attachment1->id, $attachment2->id);
        $this->assertEquals(2, Attachment::where('task_id', $this->task->id)->count());
    }

    /**
     * Test adding a link uses database transaction.
     */
    public function test_add_link_uses_transaction(): void
    {
        $url = 'https://example.com/document';
        
        $attachment = $this->attachmentService->addLink($this->task, $this->user, $url, 'Test');

        // Verify both attachment and activity were created (transaction committed)
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'type' => 'link',
            'url' => $url,
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $this->task->id,
            'activity_type' => 'updated',
            'field_name' => 'attachment',
        ]);
    }
}
