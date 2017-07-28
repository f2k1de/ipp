<?php
class DBpassword
{
	/** Dies ist eine Example-Datei
	* Damit das Programm funktioniert, muessen die Daten angepasst werden!
	*/
	private $LoginName;
	private $LoginHost;
	private $LoginAccount;
	private $LoginPassword;

	public function __constuct()
    {}



	protected function init()
	{
	  $toolaccount = "freddy2001";
	  $ts_mycnf = parse_ini_file("/mnt/nfs/labstore-secondary-tools-project/" . $toolaccount . "/replica.my.cnf");


		$this->LoginName = array( // Empfohlen: Username@wiki
			'db@localhost',
		);
		# Bitte beachten, Accounts müssen in der selben Reihenfolge genannt werden, wie bei LoginName! #
		$this->LoginHost = array( // Internetdomain
			'tools-db',
		);
		$this->LoginAccount = array( // Name das Accounts
			$ts_mycnf['user'],
		);
		$this->LoginPassword = array( // Passwort des Accounts
			$ts_mycnf['password'],
		);
	}
	protected function getLoginName ()
	{
		return serialize ($this->LoginName);
	}
	protected function getLoginHost ()
	{
		return serialize ($this->LoginHost);
	}
	protected function getLoginAccount ()
	{
		return serialize ($this->LoginAccount);
	}
	protected function getLoginDBpassword ()
	{
		return serialize ($this->LoginPassword);
	}
}
?>
