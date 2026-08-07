<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\VCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PublicVCardTest extends TestCase
{
    use RefreshDatabase;
    public function test_a_published_card_is_publicly_visible():void { $user=User::factory()->create(); $card=VCard::create(['user_id'=>$user->id,'name'=>'Himel Ahmed','slug'=>'himel','is_published'=>true,'published_at'=>now()]); $this->get('/himel')->assertOk()->assertSee('Himel Ahmed'); }
    public function test_a_draft_card_is_not_visible():void { $user=User::factory()->create(); VCard::create(['user_id'=>$user->id,'name'=>'Draft','slug'=>'draft','is_published'=>false]); $this->get('/draft')->assertNotFound(); }
}
