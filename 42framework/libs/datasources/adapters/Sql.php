<?php
namespace framework\libs\datasources\adapters;

class Sql
{
	protected $queryType = null;
	protected $fields = array();
	protected $from = null;
	protected $where = array();
	protected $orderBy = array();
	protected $groupBy = array();
	protected $limit = null;
	protected $offset = null;
	protected $parameters = array();
	
	public function select($fields = array(), $from = '')
	{
		$this->queryType = 'SELECT';
		
		$this->fields($fields);
		$this->from($from);
		
		return $this;
	}
	
	public function insert($fields = array(), $from = '')
	{
		$this->queryType = 'INSERT INTO';
		
		$this->from($from);
		$this->fieldsAndValues($fields);
		
		return $this;
	}
	
	public function update($fields = array(), $from = '')
	{
		$this->queryType = 'UPDATE';
		
		$this->from($from);
		$this->fieldsAndValues($fields);
		
		return $this;
	}
	
	public function delete($tableName = null)
	{
		$this->queryType = 'DELETE FROM';
		
		$this->from($tableName);
		
		return $this;
	}
	
	public function fields($fields = array())
	{
		foreach($fields as $f)
		{
			$this->fields[] = $f;
		}
		
		return $this;
	}
	
	public function fieldsAndValues($fields = array())
	{
		foreach($fields as $f => $v)
		{
			$this->fields[$f] = $v;
		}
		
		return $this;
	}
	
	public function from($tableName = null)
	{
		$this->from = $tableName;
		
		return $this;
	}
	
	public function where($conditions = array())
	{
		foreach($conditions as $k => $v)
		{
			if(strpos($v, '*') !== false)
            {
                $this->where["{$k} LIKE "] = str_replace('*', '%', $v);
            }
            else
            {
                $this->where["{$k} = "] = $v;
            }
		}
		
		return $this;
	}
	
	public function orderBy($fields = array())
	{
		foreach($fields as $f => $sens)
		{
			$this->orderBy[$f] = $sens;
		}
		
		return $this;
	}
	
	public function groupBy($fields = array())
	{
		foreach($fields as $f)
		{
			$this->groupBy[] = $f;
		}
		
		return $this;
	}
	
	public function limit($limit = null, $offset = null)
	{
		$this->limit = $limit;
		$this->offset = $offset;
		
		return $this;
	}
	
	public function buildQuery()
	{
		switch($this->queryType)
		{
			case 'SELECT':
				$query = $this->buildSelect();
				break;
			case 'INSERT INTO':
				$query = $this->buildInsert();
				break;
			case 'UPDATE':
				$query = $this->buildUpdate();
				break;
			case 'DELETE FROM':
				$query = $this->buildDelete();
				break;
		}
		
		return $query;
	}
	
	public buildSelect()
	{
		$query = $this->queryType.' ';
		
		$query .= implode(', ', $this->fields);
		
		$query .= ' FROM '.$this->from;
		
		if($nbWhere = count($this->where) > 0)
		{
			$query .= ' WHERE';
			
			$i = 1;
			foreach($this->where as $k => $v)
			{
				$query .= ' '.$k.'?';
				
				$this->parameters[] = $v;
				
				if($i != $nbWhere)
				{
					$query .= ' AND';
				}
				
				$i++;
			}
		}
		
		if($nbOrderBy = count($this->orderBy) > 0)
		{
			$query .= ' ORDER BY';
			
			$i = 1;
			foreach($this->orderBy as $f => $sens)
			{
				$query .= ' '.$k.' '.$v;
				
				if($i != $nbOrderBy)
				{
					$query .= ',';
				}
				
				$i++;
			}
		}
		
		if($nbGroupBy = count($this->groupBy) > 0)
		{
			$query .= ' GROUP BY';
			
			$i = 1;
			foreach($this->groupBy as $f)
			{
				$query .= ' '.$f;
				
				if($i != $nbGroupBy)
				{
					$query .= ',';
				}
				
				$i++;
			}
		}
		
		if(!empty($this->limit))
		{
			$query .= ' LIMIT '.$this->limit;
			
			if(!empty($this->offset))
			{
				$query .= ', '.$this->offset;
			}
		}
		
		return $query;
	}
	
	public buildInsert()
	{
		$query = $this->queryType.' ';
		
		$query .= $this->from;
		
		$fields = array();
		$values = array();
		
		foreach($this->fields as $f => $v)
		{
			$fields[] = $f;
			$values[] = '?';
			
			$this->parameters[] = $v;
		}
		
		$query .= ' ('.implode(', ', $fields).') ';
		$query .= 'VALUES('.implode(', ', $values).')';
		
		return $query;
	}
	
	public buildUpdate()
	{
		$query = $this->queryType.' ';
		
		$query .= $this->from.' SET';
		
		$set = array();
		
		foreach($this->fields as $f => $v)
		{
			$set[] = $f.' = ?';
			
			$this->parameters[] = $v;
		}
		
		$query .= ' '.implode(', ', $set);
		
		if($nbWhere = count($this->where) > 0)
		{
			$query .= ' WHERE';
			
			$i = 1;
			foreach($this->where as $k => $v)
			{
				$query .= ' '.$k.'?';
				
				$this->parameters[] = $v;
				
				if($i != $nbWhere)
				{
					$query .= ' AND';
				}
				
				$i++;
			}
		}
		
		return $query;
	}
	
	public builddelete()
	{
		$query = $this->queryType.' ';
		
		$query .= $this->from;
		
		if($nbWhere = count($this->where) > 0)
		{
			$query .= ' WHERE';
			
			$i = 1;
			foreach($this->where as $k => $v)
			{
				$query .= ' '.$k.'?';
				
				$this->parameters[] = $v;
				
				if($i != $nbWhere)
				{
					$query .= ' AND';
				}
				
				$i++;
			}
		}
		
		return $query;
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
}
?>