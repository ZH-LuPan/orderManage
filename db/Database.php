<?php

//数据库类
class Database
{
    private $pdo;
    private $table;
    private $where;
    private $fields = '*';
    private $limit;
    private $order;
    private $count = 'COUNT(*)';


    //构造方法，自动PDO连接数据库
    public function __construct($table)
    {
        $host = '';
        $dbName = '';
        $userName = '';
        $pwd = '';

        try {
            //连接MySQL数据库
            $pdo = new PDO('mysql:host='.$host.';dbname='.$dbName,$userName,$pwd);
            //设置UTF8字符编码
            $pdo->query('SET NAMES UTF8');
            //保存PDO对象为属性
            $this->pdo = $pdo;
            //保存数据表名
            $this->table = '`' . $table . '`';

        } catch (PDOException $e) {
            //输出错误信息
            echo $e->getMessage();
        }
    }

    //内部自我实例化，静态方法
    public static function getMysql($table = '')
    {
        return new self($table);
    }

    //原生执行SQL 语句，返回准备对象
    public function query($sql)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    //还可以返回PDO 对象，用于原生时的一些操作
    public function getPdo()
    {
        return $this->pdo;
    }

    //数据库新增操作
    public function add($data)
    {
        $keys = array_keys($data);
        foreach ($keys as $key => $value) {
            $keys[$key] = '`' . $value . '`';
        }
        foreach ($data as $key => $value) {
            $data[$key] = "'" . $value . "'";
        }
        $values = implode(',', $data);
        $sql = "INSERT INTO $this->table ($this->fields) VALUES ($values)";
        $this->pdo->prepare($sql)->execute();
        return $this->pdo->lastInsertId();
    }

    //数据库修改操作
    public function update($data)
    {
        $update = '';
        unset($data['id']);
        foreach ($data as $key => $value) {
            $update .= "`$key`='$value',";
        }
        $update = substr($update, 0, -1);
        $sql = "UPDATE $this->table SET $update $this->where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();

    }

    //数据库删除操作
    public function delete()
    {
        $sql = "DELETE FROM $this->table $this->where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }



    /**
     * @return array
     */
    public function find()
    {
        $sql = "SELECT $this->fields FROM $this->table $this->where LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $this->object_array($stmt->fetchObject());

    }

    //多查询方法
    public function select()
    {
        $sql = "SELECT $this->fields FROM $this->table $this->where $this->order $this->limit";
//        print_r($sql);die;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $objects = array();
        while ($rows = $stmt->fetchObject()) {
            $objects[] = $this->object_array($rows);
        }
        $this->count = $stmt->rowCount();
        return $objects;
    }

    //所有的where 条件都在这里
    public function where($param)
    {
        $key = key($param);
        $value = current($param);
        $sign = empty($param[0]) ? '=' : $param[0];
        $this->where = "WHERE (`$key`$sign'$value')";
        return $this;
    }

    public function wheres($param)
    {
        $this->where = "WHERE ($param)";
        return $this;
    }

    //所有的字段设置都在这里
    public function fields($fields)
    {
        $fields = explode(',', $fields);
        foreach ($fields as $key => $value) {
            $fields[$key] = "`$value`";
        }
        $this->fields = implode(',', $fields);
        return $this;
    }

    public function limit($start, $end)
    {
        $this->limit = "LIMIT $start, $end";
        return $this;
    }

    public function order($param)
    {
        $this->order = 'ORDER BY ' .$param;
        return $this;
    }

    public function count()
    {
        return $this->count;
    }

    public function counts()
    {
        $sql = "SELECT $this->count FROM $this->table  $this->where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

}
