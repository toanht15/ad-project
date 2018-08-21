<?php

namespace App\Console\Commands;

use App\Models\Author;

class OneTimeGeneratePostUsername extends BaseCommand
{
    const LIMIT = 10000;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeGeneratePostUsername';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate post username from profile url';
    
    public function doCommand()
    {
        $count = Author::select('id')->count();

        for ($i = 0; $i < ($count / self::LIMIT); $i++) {
            $authors = Author::where('username', '=', '')->select('id', 'profile_url', 'username')->limit(self::LIMIT)->get();
            foreach ($authors as $author) {
                try {
                    $username = str_replace('https://www.instagram.com/', '', $author->profile_url);
                    $author->username = $username;
                    $author->save();
                } catch (\Exception $exception) {
                    \Log::error($exception);
                    \Log::error('AuthorID ' . $author->id . ' cannot save');
                }
            }
            \Log::debug($i);
        }
    }
}
