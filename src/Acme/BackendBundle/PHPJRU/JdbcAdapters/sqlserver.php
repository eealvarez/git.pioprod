<?php

class SqlServer implements JdbcAdapterInterface
{
	public function getConexion($host,$port,$database,$user,$password)
	{
                $instance = "";
		if(!$host) $host = 'localhost';
                if(stripos($host, "\\") !== false){
                    $items = explode("\\", $host);
                    $host = $items[0];
                    $instance = ";instance=".$items[1];
                }
		
		if(!$port) $port = '1433';
		
		$conn = new JdbcConnection(
			'net.sourceforge.jtds.jdbc.Driver',
			"jdbc:jtds:sqlserver://".$host.":".$port."/".$database.$instance,
			$user,
			$password
		);
		
		return $conn;
	}
}