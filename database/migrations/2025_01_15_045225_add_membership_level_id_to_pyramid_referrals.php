<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMembershipLevelIdToPyramidReferrals extends Migration
{
    public function up()
    {
        Schema::table('pyramid_referrals', function (Blueprint $table) {
            $table->foreignId('membership_level_id')->after('referrer_id')->constrained('membership_levels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('pyramid_referrals', function (Blueprint $table) {
            $table->dropForeign(['membership_level_id']);
            $table->dropColumn('membership_level_id');
        });
    }
}
