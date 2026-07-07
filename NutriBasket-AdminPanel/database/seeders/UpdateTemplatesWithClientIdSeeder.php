<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\User;

class UpdateTemplatesWithClientIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all templates that don't have a client_id set
        $templates = Template::whereNull('client_id')->get();
        
        foreach ($templates as $template) {
            $user = User::find($template->user_id);
            
            if ($user && $user->b2bClients->isNotEmpty()) {
                // Get the first client assigned to the user
                $primaryClient = $user->b2bClients->first();
                
                // Update the template with the client_id
                $template->update([
                    'client_id' => $primaryClient->id
                ]);
                
                $this->command->info("Updated template ID {$template->id} with client ID {$primaryClient->id} for user {$user->id}");
            } else {
                $this->command->warn("Template ID {$template->id} - User {$template->user_id} has no B2B clients assigned");
            }
        }
        
        $this->command->info('Template client_id update completed!');
    }
}
