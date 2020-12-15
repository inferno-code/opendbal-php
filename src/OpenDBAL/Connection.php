<?php declare(strict_types = 1);

namespace OpenDBAL;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function \get_object_vars;

abstract class Connection {

	protected $log;
	private $silentMode;

	public function __construct(LoggerInterface $log = null, $silentMode = false) {

		$this->log = $log ?? new NullLogger();

		$this->silentMode = $silentMode;
	}

	abstract protected function doQuery(string $sql, array $parameters);

	abstract public function transactional(callable $callback);

	protected function isSilentMode() {

		return $this->silentMode;
	}

	public function query(string $sql, $parameters = []) {

		if (is_object($parameters))
			$parameters = get_object_vars($parameters);

		return $this->doQuery($sql, $parameters);
	}

	public function fetchArray(string $sql, $parameters = []) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchArray();
		$statement->close();
		return $result;
	}

	public function fetchObject(string $sql, $parameters = []) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchObject();
		$statement->close();
		return $result;
	}

	public function fetchColumn(string $sql, $parameters = [], $column = 0) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchColumn($column);
		$statement->close();
		return $result;
	}

	public function fetchAllArray(string $sql, $parameters = []) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllArray();
		$statement->close();
		return $result;
	}

	public function fetchAllAssoc(string $sql, $parameters = []) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllAssoc();
		$statement->close();
		return $result;
	}

	public function fetchAllObject(string $sql, $parameters = []) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllObject();
		$statement->close();
		return $result;
	}

	public function fetchAllDictArray(string $sql, $parameters = [], $column = 0) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllDictArray($column);
		$statement->close();
		return $result;
	}

	public function fetchAllDictAssoc(string $sql, $parameters = [], $column = null) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllDictAssoc($column);
		$statement->close();
		return $result;
	}

	public function fetchAllDictObject(string $sql, $parameters = [], $column = null) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->fetchAllDictObject($column);
		$statement->close();
		return $result;
	}

	public function eachArray(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->eachArray($callback);
		$statement->close();
		return $result;
	}

	public function eachAssoc(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->eachAssoc($callback);
		$statement->close();
		return $result;
	}

	public function eachObject(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->eachObject($callback);
		$statement->close();
		return $result;
	}

	public function mapArray(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->mapArray($callback);
		$statement->close();
		return $result;
	}

	public function mapAssoc(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->mapAssoc($callback);
		$statement->close();
		return $result;
	}

	public function mapObject(string $sql, $parameters = [], callable $callback) {

		$statement = $this->query($sql, $parameters);
		if ($statement === null)
			return null;

		$result = $statement->mapObject($callback);
		$statement->close();
		return $result;
	}

}
