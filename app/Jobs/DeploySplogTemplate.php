<?php

namespace App\Jobs;

use App\Events\SplogDeployed;
use App\Models\SplogInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SFTP;

use RuntimeException;

use function preg_replace;
use function storage_path;

final class DeploySplogTemplate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 600;


    public function __construct(private SplogInstance $instance)
    {
        $this->onQueue('splog-deployer');
    }

    /**
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    public function handle(Filesystem $storage, Dispatcher $dispatcher): void
    {
        $instance = $this->instance;

        $sftp = new SFTP($instance->serverIP);
        $sftp->setTimeout(0);
        if (!$sftp->login($instance->sshUser, $instance->sshPassword)) {
            throw new RuntimeException('SFTP login failed');
        }

        $wp_config = preg_replace(
            [
                "/'DB_NAME', ''/",
                "/'DB_USER', ''/",
                "/'DB_PASSWORD', ''/",
            ],
            [
                "'DB_NAME', '$instance->dbName'",
                "'DB_USER', '$instance->dbUser'",
                "'DB_PASSWORD', '$instance->dbPassword'",
            ],
            $storage->get('wp/wp-config.php')
        );

        $path = "webapps/$instance->runCloudAppName";
        $sftp->put("$path/wp-config.php", $wp_config);
        $sftp->put(
            "$path/wp-content.tar.gz",
            storage_path('app/wp/wp-content.tar.gz'),
            SFTP::SOURCE_LOCAL_FILE
        );
        $dbDump = preg_replace('/http:\/\/wp\.loc/', "http://$instance->domain", $storage->get('wp/dump.sql'));
        $sftp->put("$path/wp-dump.sql", $dbDump);
        $sftp->exec("tar -xzf ~/$path/wp-content.tar.gz -C $path");
        $sftp->exec("mysql -u $instance->dbUser -p'$instance->dbPassword' $instance->dbName < ~/$path/wp-dump.sql");
        $sftp->exec("rm -f ~/$path/wp-content.tar.gz ~/$path/wp-dump.sql");

        $dispatcher->dispatch(new SplogDeployed($instance));
    }

    public function displayName(): string
    {
        return 'Deploying splog template for "' . $this->instance->domain . '"';
    }
}
