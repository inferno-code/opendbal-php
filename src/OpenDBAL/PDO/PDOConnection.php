<?php declare(strict_types = 1);

namespace OpenDBAL\PDO;

use Psr\Log\LoggerInterface;
use OpenDBAL\Connection;

use \PDO;
use \Exception;
use \InvalidArgumentException;

use function \parse_url;
use function \basename;

class PDOConnection extends Connection {

	private $dsn = null;
	private $username = '';
	private $password = '';

	protected $pdo = null;

	public function __construct($url, LoggerInterface $log = null, $silentMode = false) {

		parent::__construct($log, $silentMode);

		if (is_object($url) && $url instanceof PDO) {

			$this->pdo = $url;

		} else {

			$urlDetails = parse_url($url);
			if ($urlDetails === false)
				throw new InvalidArgumentException("Unexpected URI");

			$urlDetails = (object) $urlDetails;

			if (!isset($urlDetails->scheme))
				throw new InvalidArgumentException("Unexpected URI: scheme is not defined;");

			switch ($urlDetails->scheme) {

			case 'pgsql':
			case 'postgres':
			case 'postgresql':
			case 'pdo_pgsql':
				$this->pgsqlConfigure($urlDetails);
			break;

			case 'mysql':
			case 'pdo_mysql':
				$this->mysqlConfigure($urlDetails);
			break;

			case 'sqlite':
			case 'pdo_sqlite':
				$this->sqliteConfigure($urlDetails);
			break;

			case 'mssql':
			case 'sqlsrv':
			case 'pdo_mssql':
			case 'pdo_sqlsrv':
				$this->mssqlConfigure($urlDetails);
			break;

			case 'oci':
			case 'oraclev':
			case 'pdo_oci':
			case 'pdo_oracle':
				$this->oracleConfigure($urlDetails);
			break;

			case 'ibm':
			case 'db2':
			case 'pdo_ibm':
			case 'pdo_db2':
				$this->ibmdb2Configure($urlDetails);
			break;

			default:
				throw new InvalidArgumentException("Unexpected URI: unknown scheme \"{$urlDetails->scheme}\";");

			}
		}
	}

	public function getConnectionResource() {

		if (!$this->isConnected()) {

			$this->connect();

			if (!$this->isConnected())
				return null;
		}

		return $this->pdo;
	}

	private function pgsqlConfigure($url) {

		$this->username = $url->user ?? 'postgres';
		$this->password = $url->pass ?? '';

		$host = $url->host ?? 'localhost';
		$port = $url->port ?? 5432;

		$dbnameDefault = 'postgres';
		$dbname = isset($url->path) ? basename($url->path) : $dbnameDefault;
		if ($dbname === '')
			$dbname = $dbnameDefault;

		$this->dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
	}

	private function mysqlConfigure($url) {

		$this->username = $url->user ?? 'root';
		$this->password = $url->pass ?? '';

		$host = $url->host ?? 'localhost';
		$port = $url->port ?? 3306;

		$dbnameDefault = 'mysql';
		$dbname = isset($url->path) ? basename($url->path) : $dbnameDefault;
		if ($dbname === '')
			$dbname = $dbnameDefault;

		$this->dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
	}

	private function sqliteConfigure($url) {

		$this->username = $url->user ?? '';
		$this->password = $url->pass ?? '';

		$location = $url->path ?? '';

		$this->dsn = "sqlite:{$location}";
	}

	private function mssqlConfigure($url) {

		$this->username = $url->user ?? 'sa';
		$this->password = $url->pass ?? '';

		$host = $url->host ?? 'localhost';
		$port = $url->port ?? 1433;

		$dbnameDefault = 'master';
		$dbname = isset($url->path) ? basename($url->path) : $dbnameDefault;
		if ($dbname === '')
			$dbname = $dbnameDefault;

		$this->dsn = "sqlsrv:Server={$host},{$port};Database={$dbname}";
	}

	private function oracleConfigure($url) {

		$this->username = $url->user ?? 'SYSTEM';
		$this->password = $url->pass ?? 'MANAGER';

		$dbname = isset($url->path) ? basename($url->path) : '';
		if ($dbname === '')
			throw new InvalidArgumentException("Unexpected URI: database does not specified;");

		if (isset($url->host)) {

			$host = $url->host;
			$port = $url->port ?? 1521;

			$this->dsn = "oci:dbname=//{$host}:{$port}/{$dbname}";

		} else {

			$this->dsn = "oci:dbname={$dbname}";

		}
	}

	private function ibmdb2Configure($url) {

		$username = $url->user ?? 'db2inst1';
		$password = $url->pass ?? '';

		$host = $url->host ?? 'localhost';
		$port = $url->port ?? 50000;

		$dbname = isset($url->path) ? basename($url->path) : '';
		if ($dbname === '')
			throw new InvalidArgumentException("Unexpected URI: database does not specified;");

		$this->dsn = "ibm:HOSTNAME={$host};PORT={$port};ATABASE={$dbname};UID={$username};PWD={$password};PROTOCOL=TCPIP;";
	}

	protected function connect() {

		try {

			$this->pdo = new PDO($this->dsn, $this->username, $this->password);

		} catch (Exception $ex) {

			$this->log->error($ex);
			$this->pdo = null;

			if (!$this->isSilentMode())
				throw $ex;
		}
	}

	protected function disconnect() {

		if ($this->pdo !== null)
			$this->pdo = null;
	}

	protected function isConnected() {

		return $this->pdo !== null;
	}

	protected function doQuery(string $sql, array $parameters = []) {

		try {

			$pdo = $this->getConnectionResource();

			$this->log->debug($sql, $parameters);
			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($parameters) === true)
				return new PDOStatement($stmt);

		} catch (Exception $ex) {

			$this->log->error($ex);

			if (!$this->isSilentMode())
				throw $ex;
		}

		return null;
	}

	public function transactional(callable $callback) {

		$result = null;

		try {

			$pdo = $this->getConnectionResource();

			$pdo->beginTransaction();

			$doCommit = false;

			try {

				$result = $callback($this);
				$doCommit = true;

			} catch (Exception $ex) {

				$this->log->error($ex);
			}

			if ($doCommit) {
				$pdo->commit();
			} else {
				$pdo->rollback();
			}

		} catch (Exception $trEx) {

			$this->log->error($ex);

			if (!$this->isSilentMode())
				throw $ex;
		}

		return $result;
	}
}
