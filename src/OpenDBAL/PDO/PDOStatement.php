<?php declare(strict_types = 1);

namespace OpenDBAL\PDO;

use OpenDBAL\Statement;

use \PDO;
use \Exception;

class PDOStatement extends Statement {

	private $statement;

	public function __construct($statement) {

		$this->statement = $statement;
	}

	public function close() {

		$this->statement->closeCursor();
	}

	public function fetchAssoc() {

		$row = $this->statement->fetch(PDO::FETCH_ASSOC);

		return $row === false ? null : $row;
	}

	public function fetchArray() {

		$row = $this->statement->fetch(PDO::FETCH_NUM);

		return $row === false ? null : $row;
	}

	public function fetchObject() {

		$row = $this->statement->fetch(PDO::FETCH_OBJ);

		return $row === false ? null : $row;
	}
}
