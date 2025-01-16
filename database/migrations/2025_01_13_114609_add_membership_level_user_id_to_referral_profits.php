<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMembershipLevelUserIdToReferralProfits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_profits', function (Blueprint $table) {
            // Add the membership_level_user_id column
            $table->foreignId('membership_level_user_id')
                ->nullable() // Allow null for backward compatibility
                ->constrained('membership_level_user') // Reference the membership_level_user table
                ->onDelete('set null') // Set to null if the referenced row is deleted
                ->after('membership_level_id'); // Position the column after membership_level_id
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_profits', function (Blueprint $table) {
            // Drop the foreign key and the column
            $table->dropForeign(['membership_level_user_id']);
            $table->dropColumn('membership_level_user_id');
        });
    }
}
