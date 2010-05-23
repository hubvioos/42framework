<?php
namespace framework\libs\datasources;
use framework\libs\interfaces as I;

class PdoDatasource implements I\CrudDatasource, I\Datasource, I\DbDatasource, I\TransactionDatasource
{
	protected $db = null;
	protected $tableName = null;
	protected $primaryKey = null;
	protected $isNew = true;
	protected $modifications = array();
	
	public function __construct($connexion = 'default', $tableName = __CLASS__, $primaryKey = 'id')
	{
		$this->db = \framework\libs\DbProvider::getConnexion($connexion);
		$this->tableName = $tableName;
		$this->primaryKey = $primaryKey;
		$this->isNew = true;
	}
	
	public function __set($key, $value)
	{
		$this->$key = $value;
		$this->modifications[] = $key;
	}
	
	public function __get($key)
	{
		return $this->$key;
	}
	
	protected function getMembers()
    {
        $prop = array();

        $reflect = new \ReflectionObject($this);

        foreach($reflect->getProperties(\ReflectionProperty::IS_PROTECTED) as $var)
        {
            if(!$var->isStatic() && $var->getDeclaringClass()->getName() == get_class($this))
            {
                $prop[] = $var->getName();
            }
        }

        return $prop;
    }
    
    protected function getValues()
    {
        $values = array();

        foreach($this->getMembers() as $attr)
        {
            $values[$attr] = $this->$attr;
        }

        return $values;
    }
	
	public function find($fields, $from, $where = array(), $order = null, $limit = null, $offset = null)
	{
		$query = new adapters\Sql;
		$query->select($fields, $from)->where($where)->orderBy($order)->limit($limit, $offset);
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
		$result = $statement->setFetchMode(PDO::FETCH_OBJ);
		
		return $result;
	}
	
	public function count($field, $from, $where = array())
	{
		$query = new adapters\Sql;
		$query->select(array('COUNT('.$field.')'), $from)->where($where);
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
		$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        
        return (integer)$result[0]['COUNT('.$field.')'];
	}
	
	public function beforeSave()
	{
		return true;
	}
	
	public function afterSave()
	{
		return true;
	}
	
	public function save()
	{
		$this->beforeSave();
		
		if($this->isNew)
		{
			$this->create();
		}
		else
		{
			$this->update();
		}
		
		$this->afterSave();
	}
	
	public function create()
	{
		$query = new adapters\Sql;
		
		$query->insert($this->getValues(), $this->tableName);
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
		
		$pk = $this->primaryKey;
		
		$this->$pk = $this->db->lastInsertId();
	}
	
	public function update()
	{
		$query = new adapters\Sql;
		$pk = $this->primaryKey;
		
		$fields = array();
		
		foreach($this->modifications as $f)
		{
			$fields[$f] = $this->$f;
		}
		
		$query->update($fields, $this->tableName)->where(array($pk => $this->$pk));
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
	}
	
	public function read()
	{
		$query = new adapters\Sql;
		$pk = $this->primaryKey;
		$query->select(array('*'), $this->tableName)->where(array($pk => $this->$pk));
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
		$result = $statement->setFetchMode(PDO::FETCH_OBJ);
		
		foreach($this->getMembers() as $prop)
		{
			$this->$prop = $result->$prop;
		}
		
		$this->isNew = false;
	}
	
	public function delete()
	{
		$query = new adapters\Sql;
		$pk = $this->primaryKey;
		$query->delete($this->tableName)->where(array($pk => $this->$pk));
		
		$statement = $this->db->prepare($query->buildQuery());
		$this->db->execute($query->getParameters());
	}
	
	public function exec($query)
	{
		return $this->db->exec($query);
	}
	
	public function query($query)
	{
		return $this->db->query($query);
	}
	
	public function describeTable()
	{
		$sql = 'DESCRIBE '.$this->$tableName;
		$query = $this->db->prepare($sql);
		
		$query->execute();
		
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	public function beginTransaction()
	{
		$this->db->beginTransaction();
	}
	
	public function commit()
	{
		$this->db->commit();
	}
	
	public function rollBack()
	{
		$this->db->rollBack();
	}
}
?>