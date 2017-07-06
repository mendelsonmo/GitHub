# GitHub

DOIS exemplo de carregamento de classe dentro de diretorio
estrutura do projeto
app/
_classes/
_models/
_autoloade/
_admin/
_site/


************************* PRIMEIRO FORMATO CARREGAMENTO DE CLASSES *******************************************
SALVAR PAGINA COM NOME autoloade.php
<?php
define('HOST','localhost');    // BANCO MYSQL
define('USER','root');         // USUARIO
define('PASS','');             //SENHA
define('DBA','');              //NOME DO BANCO

Class autoloade(){
	
	
	  public $Diretorio = ['_classes','_models']; //**************DIRETORIOS DEFINIDOS
    public $iDir = null;  //**************DIRETORIOS VAZIOS
	
	public function __construction(){   //*********CARREGAMENTO 
		spl_autoload_register(array($this, 'loader'));
	}
  
	private static loader($Class){		
		foreach ($Diretorio as $dirName):
            if (!$iDir && file_exists("../{$dirName}/{$Class}.class.php")):
                    require_once("../{$dirName}/{$Class}.class.php");
                      $iDir = true;
            endif;
        endforeach;
    if (!$iDir):
        echo "Não foi possível incluir {$Class}.class.php",E_USER_ERROR;
        die;
    endif;		
	}	
}
?>

***********************  SEGUNDO FORMATO DE CARREGAMENTO DE CLASSES ***********************************************

   ***********************     SALVAR PAGINA COM NOME autoloade.php **********************************************
                                          
<?php
define('HOST','localhost');  // BANCO MYSQL
define('USER','root');      // USUARIO
define('PASS','');          //SENHA
define('DBA','');            //NOME DO BANCO

// AUTO LOAD DE CLASSES ####################
function __autoload($Class) {

       $cDir = ['_config','_models'];
       $iDir = null;

        foreach ($cDir as $dirName):
             if (!$iDir && file_exists("../{$dirName}/{$Class}.class.php")):
                    require_once("../{$dirName}/{$Class}.class.php");
                      $iDir = true;
            endif;
        endforeach;

    if (!$iDir):
        echo "Não foi possível incluir {$Class}.class.php",E_USER_ERROR;
        die;
    endif;
}
?>

************************************** CONEXAO COM BANCO DE DADOS ****************************************************

SALVAR DENTRO DO DIRETORIO  _classes/ COM O NOME DE Conn.class.php  OS PARAMETROS JÁ FORAM FEITOS ATRAVES DOS autoload.php

<?php
/*
*conexao
*/
class Conn{
    private static $Host = HOST;
    private static $User = USER;
    private static $Pass = PASS;
    private static $Dba  = DBA;


    private static $Connect = null;


    private static function Conectar() {
        try {
            if (self::$Connect == null):
                $dsn = 'mysql:host=' . self::$Host . ';dbname=' . self::$Dba;
                $options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
                self::$Connect = new PDO($dsn, self::$User, self::$Pass, $options);
            endif;
        } catch (PDOException $e) {
            PHPErro($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            die;
        }

        self::$Connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$Connect;
    }

    /** Retorna um objeto PDO Singleton Pattern. */
    public static function getConn() {
        return self::Conectar();
    }
}
?>
**************************************** CLASSE GENERICA PARA SELECT   ****************************************
SALVAR DENTRO DO DIRETORIO  (_classes/) COM O NOME DE Read.class.php  SERA CARREGADA AUTOMATICA autoload.php
<?php
class Read extends Conn {

    private $Select;
    private $Result;
    private $Read;
    private $Conn;
    
// RETORNA UM SELECT EM JSON
    public function ExeRead($Tabela, $Termos = null) {

        $this->Select = "SELECT * FROM {$Tabela} {$Termos}";
        $this->Execute();  //FUNCAO
    }
     //RETORNA UM REGISTRO NO BANCO COMO STRING
    public function ReadString($Tabela, $Termos = null) {
        $this->Select = "SELECT * FROM  {$Tabela} {$Termos}";
        $this->ExecuteReadString();  //FUNCAO
    }
      //RETORNA TODOS OS REGISTRO NO BANCO COM STRING
    public function ExeString($Tabela, $Termos = null) {
        $this->Select = "SELECT * FROM  {$Tabela} {$Termos}";
        $this->ExecuteStringAll();   //FUNCAO
    }
    // FUNCAO PARA EXECUTAR UM STRING E RETORNA UM REGISTRO
    public function ExecuteReadString() {
        $this->Connect();
        try {
            $this->Read = $this->Conn->prepare($this->Select);
            $this->Read->execute();
            $this->Result = $this->Read->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $this->Result = null;
            echo "<b>Erro ao Ler:</b> {$ex->getMessage()}", $ex->getCode();
        }
    }
     // FUNCAO PARA EXECUTAR UM STRING E RETORNA TODOS OS REGISTROS
    public function ExecuteStringAll() {
        $this->Connect();
        try {
            $this->Read = $this->Conn->prepare($this->Select);
            $this->Read->execute();
            $this->Result = $this->Read->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $this->Result = null;
            echo "<b>Erro ao Ler:</b> {$ex->getMessage()}", $ex->getCode();
        }
    }
    // RETORNA O RESULTADO DO SELECT
    public function getResult() {
        return $this->Result;  
    }

    //Conexao com banco de Dados
    private function Connect() {
        $this->Conn = parent::getConn();
    }

    //Contar numero de colunas
    public function getRowCount() {
        return $this->Read->rowCount();
    }

   //executa o retorno do json
    private function Execute() {

        $this->Connect();
        try {
            $this->Read = $this->Conn->prepare($this->Select);
            $this->Read->execute();
            while ($this->Result = $this->Read->fetchAll(PDO::FETCH_ASSOC)) {
                $output = ['data' => $this->Result];
                $obj = json_encode($output);
                echo $obj;
            }
        } catch (PDOException $e) {
            $this->Result = null;
            echo "<b>Erro ao Ler:</b> {$e->getMessage()}", $e->getCode();
        }
    }
}

?>
**************************************** CLASSE GENERICA PARA INSERIR   ****************************************
SALVAR DENTRO DO DIRETORIO  (_classes/) COM O NOME DE Create.class.php  SERA CARREGADA AUTOMATICA autoload.php

<?php
class Create extends Conn {

    
    private $Tabela;
    private $Dados;
    private $Result;

    /** @var PDOStatement */
    private $Create;

    /** @var PDO */
    private $Conn;

    /**
     * <b>ExeCreate:</b> Executa um cadastro simplificado no banco de dados utilizando prepared statements.
     * Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor!
     * 
     * @param STRING $Tabela = Informe o nome da tabela no banco!
     * @param ARRAY $Dados = Informe um array atribuitivo. ( Nome Da Coluna => Valor ).
     */
    public function ExeCreate($Tabela, array $Dados) {
        $this->Tabela = (string) $Tabela;
        $this->Dados = $Dados;

        $this->getSyntax();
        $this->Execute();
    }

    /**
     * <b>Obter resultado:</b> Retorna o ID do registro inserido ou FALSE caso nem um registro seja inserido! 
     * @return INT $Variavel = lastInsertId OR FALSE
     */
    public function getResult() {
        return $this->Result;
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    //Obtém o PDO e Prepara a query
    private function Connect() {
        $this->Conn = parent::getConn();
        $this->Create = $this->Conn->prepare($this->Create);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax() {
        $Fileds = implode(', ', array_keys($this->Dados));
        $Places = ':' . implode(', :', array_keys($this->Dados));
        $this->Create = "INSERT INTO {$this->Tabela} ({$Fileds}) VALUES ({$Places})";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function Execute() {
        $this->Connect();
        try {
            $this->Create->execute($this->Dados);
            $this->Result = $this->Conn->lastInsertId();
        } catch (PDOException $e) {
            $this->Result = null;
            echo "<b>Erro ao cadastrar:</b> {$e->getMessage()}", $e->getCode();
        }
    }
}
?>
qwrwqr
