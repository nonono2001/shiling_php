<?php


Class SqlClass
{
	private $conn;

	function connect()
	{
		$db_config = getSetting( 'sys_setting' );
		$db_host = $db_config['db_host'];
		$db_user = $db_config['db_user'];
		$db_pass = $db_config['db_pass'];
		$db_name = $db_config['db_name'];
		
		//connect db
		//参考http://www.cnblogs.com/cobbliu/p/4218196.html，可知，一个进程中多次连接同一数据库，不会生成多个连接句柄。
		//这样就可以放心地多次new SqlClass了。
		$this->conn = mysql_connect($db_host, $db_user, $db_pass);
		if(!$this->conn)
		{
			echo "Connect db fail! Exit!<br/>".mysql_error();
			exit();
		}

		//select db
		if( !mysql_select_db($db_name) )
		{
			echo "Select db fail! Exit!<br/>".mysql_error();
			mysql_close($this->conn);
			exit();
		}

        //set charactor set
		$sql = "set names 'utf8'";
		if( !mysql_query($sql) )
		{
			echo "Set char set fail! Exit!<br/>".mysql_error();
			mysql_close($this->conn);
			exit();
		}
		
		//参考http://zhidao.baidu.com/link?url=pnd5soDzEzugVfFyguU1BG98uMAMjZIqwJ6ngzYFJDp8FunB1yJ3m98COBzrR2Xza_6Re3Yo1QQmst7dPO1Haq
		//设置时区，要不然你发的文章时间会比实际时间早8个小时
		//mysql_query("set time_zone = '+8:00';");
	}
   

  	function close()
  	{
  		if( !mysql_close($this->conn) )
		{
//			echo "Close db fail! Exit<br />".mysql_error();
//			exit();
			//有可能多次关闭会导致关闭失败，但无所谓了，只要关闭就好。所以也不用echo出去了。
		}
  	}


	function Query($sql)
	{
		$result = mysql_query($sql);
		if( !$result )
		{
			echo "Execute sql fail! Exit!<br/>".mysql_error();
			mysql_close($this->conn);
			exit();
		}
		return $result;
	}
	
	function GetRow($query)
	{
		$row = mysql_fetch_array($query);
		return $row;
	}
	
	//参考tuiarv4的DB类的静态成员函数static function field
	//参数$array是一个数组，函数功能：将数组粘连成一串字符串，中问用$glue粘连。
	//最终结果比如：username='jack', sex='male', age='20'
	function field($array, $glue = ',', $is_where=0)
	{
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$s = '';
			if(is_array($v)) {
				$g = $v['glue'];
				if($g) {
					$kk = ($v['key'] ? $v['key'] : $k);
					$vv = $v['val'];
					switch ($g) {
						case '=':
						case '>':
						case '<':
						case '<>':
						case '>=':
						case '<=':
							$s = "`{$kk}`{$g}'{$vv}'";
							break;
						case '-':
						case '+':
						case '|':
						case '&':
						case '^':
							$s = "`{$kk}`=`{$kk}`{$g}'{$vv}'";
							break;
						case 'like':
							$s = "`{$kk}` LIKE('{$vv}')";
							break;
						case 'in':
						case 'notin':
							$s = "`{$kk}`".('notin'==$g ? ' NOT' : '')." IN(".jimplode($vv).")";
							break;
						default:
							exit("glue $g is invalid");
					}
				} else {
					if($is_where) {
						$s = "`{$k}` IN(".jimplode($v).")";
					}
				}
			} else {
				$s = "`{$k}`='$v'";
			}

			if($s) {
				$sql .= $comma . $s;
				$comma = $glue;
			}
		}
		return $sql;
	}
	
	//该函数是给表名加前缀的，只不过我这里没用前缀。
	function table($table)
	{
		$table_name = $table;
		return $table_name;
	}
	
	function where($condition, $glue=' AND ')
	{
		if(empty($condition)) {
			$where = '';
		} elseif(is_array($condition)) {
			$where = ' WHERE ' .$this->field($condition, $glue, 1);
		} else {
			$where = ' ' . (false!==strpos(strtoupper($condition), 'WHERE ') ? $condition : 'WHERE ' . $condition);
		}
		$where .= ' ';
		return $where;
	}
	
	function update($table, $data, $condition)
	{
		$sql = $this->field($data);
		$cmd = "UPDATE ";
		$table = $this->table($table);
		$res = $this->Query( "$cmd $table SET $sql ".$this->where($condition) );
		return $res;
	}
	
	function delete($table, $condition, $return_delete_num = false, $limit = 0)
	{
		$sql = "DELETE FROM ".$this->table($table).$this->where($condition).($limit ? "LIMIT $limit" : '');
		$return = $this->Query($sql);
		if($return_delete_num)
		{
			return mysql_affected_rows();
		}
		else {
			return $return;
		}
	}
	
	function insert($table, $data, $return_insert_id = false, $replace = false)
	{
		$sql = $this->field($data);
		
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
	
		$table = $this->table($table);
		
		$return = $this->Query("$cmd $table SET $sql");
	
		if($return_insert_id)
		{
			return mysql_insert_id();//得到刚才插入到表中的记录Id。
			/*这个方法，对于主键不是AUTO_INCREMENT的id不适用，mysql_insert_id()返回的一直是0。
     在一张表中顶多只能有一个字段是AUTO_INCREMENT的。就算是主键是多个字段组合的，也只能分配其中一个为AUTO_INCREMENT。
     所以，事实上mysql_insert_id()函数，只会返回有AUTO_INCREMENT属性的那个主键的最新插入值。
     只要是自增的id，不管你插入的时候是不是写明了id，返回都是正确的。只要是不自增的id，就算你在insert时写明了id，返回的还是0。
   所以将本函数的$return_insert_id参数设为true时，要事先观察你要插入的表的主键，看它是否是auto_increment的。是的话，才能用该功能。
			 */
		}
		else 
		{
			return $return;//insert的返回值：真，表示插入成功；否则语法错误
		}
	
	}
	
	
	
	
	

}
?>
