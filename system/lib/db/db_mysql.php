<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");


class db_mysql {

    public $host;

    public $user;

    public $pass;

    public $db;

    public $config;
    
    public $connect;

    function __construct($config) {

        $this->config   = $config;
        $this->host     = $config['host'];
        $this->user     = $config['user'];
        $this->pass     = $config['pass'];
        $this->db       = $config['db'];
        $this->connect  = $this->connect();
    }

    public function connect() {

        $db = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if( @$db->connect_errno ) {
            $error = $db->connect_error;

            die("~ERROR DB: {$error}");
        }

        return $db;
    }
    
    public function query($sql,$query=array()){
        
        $connect = $this->connect;
        
        if( $query )  {
            
            $new_query = [];
            foreach($query as $q){
                $new_query[] = mysqli_real_escape_string($connect,$q);
            }
            $query = $new_query;

            foreach($query as $val){
                $position = strpos($sql,"?");

                if( $val == '0' ) $val = '0';
                else $val = "'$val'";

                $sql = substr_replace($sql,"{$val}",$position,1);
            }
        }
        
        return $this->connect->query($sql);
    }
    
    public function GetOne($sql,$query=array()) {
        $result = $this->query($sql,$query);
        
        if( @$result->num_rows == 0 ) return false;
        
        return $result->fetch_assoc();
    }
    
    public function insert($table,$array){
        
        $query = "INSERT INTO {$table}";
        
        $column = $value = '(';
        foreach($array as $key => $val) {
            $column .= "{$key},";
            $value  .= "'".mysqli_real_escape_string($this->connect,$val)."',";
        }
        
        $column = rtrim($column,",");
        $value  = rtrim($value,",");
        
        $query .= $column . ") VALUE {$value})";
        $insert = $this->query($query);
        
        if( !$insert ) return false;
        return $this->last_insert_id();
    }

    public function update($table,$array,$clause){

        $query = "UPDATE {$table} SET ";

        foreach($array as $key => $value){
            if( preg_match("/".$key."/",$value) && preg_match('/[^\+-]/',$value) ){
                $query .= "{$key} = $value,";
            } else {
                $query .= "{$key} = '$value',";
            }
        }

        $query = rtrim($query,",");
        $query .= " WHERE {$clause}";

        return $this->query($query);
    }

    public function last_insert_id(){
        $q = $this->query("SELECT LAST_INSERT_ID() as id");

        $q = $q->fetch_object();

        return $q->id ? $q->id : 0;
    }
}