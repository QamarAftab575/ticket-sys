<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Test that a new User has a UUID primary key.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_has_uuid_primary_key(): void
    {
        $user = User::factory()->create();

        // Verify the ID is not null
        $this->assertNotNull($user->id);

        // Verify the ID is a valid UUID format (RFC 4122)
        // Convert to string if it's a UUID object
        $idString = (string) $user->id;
        $this->assertTrue(Str::isUuid($idString));
    }

    /**
     * Test that multiple Users have unique UUIDs.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_multiple_users_have_unique_uuids(): void
    {
        $users = User::factory()->count(10)->create();

        $uuids = $users->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        // Verify all UUIDs are unique
        $this->assertCount(10, array_unique($uuids));

        // Verify no duplicates exist
        $this->assertEquals(count($uuids), count(array_unique($uuids)));
    }

    /**
     * Test that User UUID is generated on creation.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_uuid_is_generated_on_creation(): void
    {
        // Create a user without specifying an ID
        $user = User::factory()->create();

        // Verify the UUID was auto-generated
        $this->assertNotNull($user->id);
        $idString = (string) $user->id;
        $this->assertTrue(Str::isUuid($idString));

        // Verify it's stored in the database
        $this->assertDatabaseHas('users', [
            'id' => $idString,
        ]);
    }

    /**
     * Test that User UUID format is RFC 4122 compliant.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_uuid_format_is_rfc4122_compliant(): void
    {
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            // Verify UUID format matches RFC 4122 pattern
            // Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
            $idString = (string) $user->id;
            $this->assertMatchesRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                $idString
            );
        }
    }

    /**
     * Test that User model has string key type.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_model_has_string_key_type(): void
    {
        $user = new User();

        // Verify key type is string
        $this->assertEquals('string', $user->getKeyType());

        // Verify auto-increment is disabled
        $this->assertFalse($user->getIncrementing());
    }

    /**
     * Test that User UUID persists across database queries.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_uuid_persists_across_queries(): void
    {
        $originalUser = User::factory()->create();
        $originalId = (string) $originalUser->id;

        // Retrieve the user from database
        $retrievedUser = User::find($originalId);

        // Verify the UUID is the same
        $this->assertEquals($originalId, (string) $retrievedUser->id);
        $this->assertTrue(Str::isUuid((string) $retrievedUser->id));
    }

    /**
     * Test that User UUID is immutable after creation.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_uuid_is_immutable_after_creation(): void
    {
        $user = User::factory()->create();
        $originalId = (string) $user->id;

        // Attempt to update the user (without changing ID)
        $user->update(['name' => 'Updated Name']);

        // Verify the UUID hasn't changed
        $this->assertEquals($originalId, (string) $user->id);
        $this->assertEquals($originalId, (string) $user->fresh()->id);
    }

    /**
     * Test that User UUID is unique across all users in database.
     *
     * **Validates: Requirements 13.1**
     */
    public function test_user_uuid_is_unique_across_all_users(): void
    {
        // Create multiple batches of users
        User::factory()->count(20)->create();

        // Get all user IDs from database
        $allIds = User::pluck('id')->map(fn ($id) => (string) $id)->toArray();

        // Verify all IDs are unique
        $this->assertCount(20, array_unique($allIds));
    }
}
