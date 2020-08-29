<?php

namespace App\Console\Commands;

use App\Model\jwxt\CountDown;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use \Redis;
use Illuminate\Support\Facades\Log;
class DeleteUnusedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除未调用图片';

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
     * @return mixed
     */
    public function handle()
    {
        Log::info("It works！");
        $redis_files = [];
        try {                          //遍历redis
            $redis = new Redis();
            $redis->connect('gong_redis', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $files = $redis->hkeys("images");
        foreach ($files as $file){           //遍历结果去掉前缀
            $redis_replace = str_replace(env("APP_URL")."/storage/image/","",$file);
            $redis_files[] = $redis_replace;
        }
        print_r($redis_files);

        //删除文件

        $disk = Storage::disk('img');
        foreach ($redis_files as $file){   //遍历删除
            $disk->delete($file);
        }

//        return msg(0,__LINE__);
    }
}
