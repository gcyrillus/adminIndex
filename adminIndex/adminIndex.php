<?php
    if(!defined('PLX_ROOT')) {
        die('Nop!');
    }

    class adminIndex extends plxPlugin {
        const HOOKS = array(
            'AdminIndexTop',
        );
		
        const BEGIN_CODE = '<?php' . PHP_EOL;
        const END_CODE = PHP_EOL . '?>';
		
        public function __construct($default_lang) {
            # appel du constructeur de la classe plxPlugin (obligatoire)
            parent::__construct($default_lang);

            # Ajoute des hooks
            foreach(self::HOOKS as $hook) {
                $this->addHook($hook, $hook);
            }
        }

		
		#remplace le formulaire d'affichage des articles
        public function AdminIndexTop() {
			
            echo self::BEGIN_CODE;
?>			
		 include(PLX_ROOT.'plugins/'.__CLASS__.'/article.php');
<?php
            echo self::END_CODE;	
        }
    }