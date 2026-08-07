<?php
namespace Database\Seeders;
use App\Models\Plan;
use App\Models\Theme;
use App\Models\User;
use App\Models\VCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole=Role::firstOrCreate(['name'=>'admin','guard_name'=>'web']); Role::firstOrCreate(['name'=>'member','guard_name'=>'web']);
        $admin=User::firstOrCreate(['email'=>'admin@taply.me'],['name'=>'Taply Admin','password'=>Hash::make('password'),'email_verified_at'=>now()]); $admin->assignRole($adminRole);
        foreach ([['Starter','starter',0,0,false],['Professional','professional',9,86,true],['Business','business',29,278,true]] as [$name,$slug,$monthly,$yearly,$premium]) Plan::updateOrCreate(['slug'=>$slug],['name'=>$name,'price_monthly'=>$monthly,'price_yearly'=>$yearly,'currency'=>'USD','features'=>['qr_code','analytics',...($premium?['custom_domain','appointments','lead_capture']:[])],'is_active'=>true]);
        foreach ([['Minimal','minimal',false],['Editorial','editorial',true],['Obsidian','obsidian',true]] as [$name,$slug,$premium]) Theme::updateOrCreate(['slug'=>$slug],['name'=>$name,'is_premium'=>$premium,'is_active'=>true,'schema'=>['accent'=>'#dfff72','font'=>'DM Sans']]);
        $demo=User::firstOrCreate(['email'=>'himel@studio.com'],['name'=>'Himel Ahmed','password'=>Hash::make('password'),'email_verified_at'=>now()]);
        $demo->assignRole('member');
        $theme=Theme::where('slug','minimal')->first();
        $card=VCard::updateOrCreate(['slug'=>'himel'],['user_id'=>$demo->id,'theme_id'=>$theme?->id,'name'=>'Himel Ahmed','job_title'=>'Brand Designer & Creative Director','about'=>'I turn ambitious ideas into clear, memorable brands. For the past eight years, I have partnered with founders and thoughtful teams to create identities and digital experiences that feel honest, useful, and built to last.','phone'=>'+8801712345678','whatsapp'=>'+8801712345678','email'=>'hello@himel.studio','website'=>'https://himel.studio','location'=>'Dhaka, Bangladesh','status'=>'published','is_published'=>true,'available_for_work'=>true,'published_at'=>now()]);
        $card->socialLinks()->updateOrCreate(['platform'=>'linkedin'],['url'=>'https://linkedin.com','is_visible'=>true]);
        $card->services()->updateOrCreate(['title'=>'Brand identity & systems'],['description'=>'Strategy, naming, visual identity, and practical guidelines.','is_visible'=>true]);
    }
}
