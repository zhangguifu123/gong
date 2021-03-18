<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:UserStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动更新用户状态,解除禁言';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //查找需要更新状态的用户
        $time = date('Y-m-d');
        $bannedUser = DB::table('User')->whereTime('updated_at',$time)->get('id')->toArray();
        if(!$bannedUser){
            return msg(4,__LINE__);
        }
        $updateStatus = DB::table('User')->find($bannedUser)->update();
        return 0;
    }
}
