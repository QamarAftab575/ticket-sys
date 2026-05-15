<?php

namespace Tests\Unit;

use App\Models\Invitation;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationModelTest extends TestCase
{
    /**
     * Test that a new Invitation has a UUID primary key.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_has_uuid_primary_key(): void
    {
        $invitation = Invitation::factory()->create();

        // Verify the ID is not null
        $this->assertNotNull($invitation->id);

        // Verify the ID is a valid UUID format (RFC 4122)
        // Convert to string if it's a UUID object
        $idString = (string) $invitation->id;
        $this->assertTrue(Str::isUuid($idString));
    }

    /**
     * Test that multiple Invitations have unique UUIDs.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_multiple_invitations_have_unique_uuids(): void
    {
        $invitations = Invitation::factory()->count(10)->create();

        $uuids = $invitations->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        // Verify all UUIDs are unique
        $this->assertCount(10, array_unique($uuids));

        // Verify no duplicates exist
        $this->assertEquals(count($uuids), count(array_unique($uuids)));
    }

    /**
     * Test that Invitation UUID is generated on creation.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_uuid_is_generated_on_creation(): void
    {
        // Create an invitation without specifying an ID
        $invitation = Invitation::factory()->create();

        // Verify the UUID was auto-generated
        $this->assertNotNull($invitation->id);
        $idString = (string) $invitation->id;
        $this->assertTrue(Str::isUuid($idString));

        // Verify it's stored in the database
        $this->assertDatabaseHas('invitations', [
            'id' => $idString,
        ]);
    }

    /**
     * Test that Invitation UUID format is RFC 4122 compliant.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_uuid_format_is_rfc4122_compliant(): void
    {
        $invitations = Invitation::factory()->count(5)->create();

        foreach ($invitations as $invitation) {
            // Verify UUID format matches RFC 4122 pattern
            // Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
            $idString = (string) $invitation->id;
            $this->assertMatchesRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                $idString
            );
        }
    }

    /**
     * Test that Invitation model has string key type.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_model_has_string_key_type(): void
    {
        $invitation = new Invitation();

        // Verify key type is string
        $this->assertEquals('string', $invitation->getKeyType());

        // Verify auto-increment is disabled
        $this->assertFalse($invitation->getIncrementing());
    }

    /**
     * Test that Invitation UUID persists across database queries.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_uuid_persists_across_queries(): void
    {
        $originalInvitation = Invitation::factory()->create();
        $originalId = (string) $originalInvitation->id;

        // Retrieve the invitation from database
        $retrievedInvitation = Invitation::find($originalId);

        // Verify the UUID is the same
        $this->assertEquals($originalId, (string) $retrievedInvitation->id);
        $this->assertTrue(Str::isUuid((string) $retrievedInvitation->id));
    }

    /**
     * Test that Invitation UUID is immutable after creation.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_uuid_is_immutable_after_creation(): void
    {
        $invitation = Invitation::factory()->create();
        $originalId = (string) $invitation->id;

        // Attempt to update the invitation (without changing ID)
        $invitation->update(['token' => Str::random(64)]);

        // Verify the UUID hasn't changed
        $this->assertEquals($originalId, (string) $invitation->id);
        $this->assertEquals($originalId, (string) $invitation->fresh()->id);
    }

    /**
     * Test that Invitation UUID is unique across all invitations in database.
     *
     * **Validates: Requirements 13.2**
     */
    public function test_invitation_uuid_is_unique_across_all_invitations(): void
    {
        // Create multiple batches of invitations
        Invitation::factory()->count(20)->create();

        // Get all invitation IDs from database
        $allIds = Invitation::pluck('id')->map(fn ($id) => (string) $id)->toArray();

        // Verify all IDs are unique
        $this->assertCount(20, array_unique($allIds));
    }
}
