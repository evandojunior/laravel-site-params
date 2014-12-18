<?php

use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class EditableCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'editable:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca elementos ".editable e .imgEditable" nas views e gera migrations/seeds de tb_site_params';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {

        /*
         * Buscando elementos..
         */
        $editables = array();

        foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '*.php') as $filename) {
            $crawler = new Crawler();
            $crawler->addContent(utf8_decode(file_get_contents($filename)));

            $editables[] = $crawler->filter('.editable')->each(function (Crawler $node) {
                return array('id' => $node->attr('id'), 'value' => $node->text());
            });
        }

        $migration = $this->migration($editables);
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '1970_01_01_000000_create_tb_site_params.php', $migration);
        
        $seed = $this->seed($editables);
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'SiteParamsSeeder.php', $seed);
    }

    private function migration($editables) {
        $data = "
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbSiteParams extends Migration {


    public function up() {
        Schema::create('tb_site_params', function(Blueprint \$table) {
            \$table->increments('id');" . PHP_EOL;

        foreach ($editables as $editable) {
            foreach ($editable as $row) {
                $data .= "            \$table->text('" . $row['id'] . "');" . PHP_EOL;
            }
        }

        $data .= " });
    }

    public function down() {
        Schema::drop('tb_site_params');
    }

}";
        return $data;
    }

    private function seed($editables) {
        $data = "
<?php

class SiteParamsSeeder extends Seeder {

    public function run() {

        DB::table('tb_site_params')->delete();

        DB::table('tb_site_params')->insert(array(" . PHP_EOL;
        
        foreach ($editables as $editable) {
            foreach ($editable as $row) {
                $data .= "'" . $row['id'] . "' => '" . $row['value'] . "'," . PHP_EOL;
            }
        }
        
        $data .= "));
    }

}";
        return $data;
    }

}
