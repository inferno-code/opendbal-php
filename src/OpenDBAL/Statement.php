<?php declare(strict_types = 1);

namespace OpenDBAL;

use function \array_values;
use function \is_int;

abstract class Statement {

	abstract public function close();

	abstract public function fetchAssoc();

	public function fetchArray() {

		$row = $this->fetchAssoc();

		if ($row === null)
			return null;

		return array_values($row);
	}

	public function fetchObject() {

		return (object) $this->fetchAssoc();
	}

	public function fetchColumn($column = 0) {

		$row = is_int($column) ? $this->fetchArray() : $this->fetchAssoc();

		if ($row === null)
			return null;

		return $row[$column] ?? null;
	}

	public function fetchAllArray() {

		$result = [];

		while (($row = $this->fetchArray()) !== null)
			$result[] = $row;

		return $result;
	}

	public function fetchAllAssoc() {

		$result = [];

		while (($row = $this->fetchAssoc()) !== null)
			$result[] = $row;

		return $result;
	}

	public function fetchAllObject() {

		$result = [];

		while (($row = $this->fetchObject()) !== null)
			$result[] = $row;

		return $result;
	}

	public function fetchAllDictArray($column = 0) {

		$results = [];

		while (($row = $this->fetchArray()) !== null) {

			$key = $row[$column] ?? null;
			unset($row[$column]);
			$row = array_values($row);
			if (count($row) === 1)
				$row = $row[0];

			$results[$key] = $row;
		}

		return $results;
	}

	public function fetchAllDictAssoc($column = null) {

		$results = [];

		while (($row = $this->fetchAssoc()) !== null) {

			if ($column === null)
				$column = $this->getFirstKey($row);

			$key = $row[$column] ?? null;
			unset($row[$column]);
			if (count($row) === 1)
				$row = $this->getFirstValue($row);

			$results[$key] = $row;
		}

		return $results;
	}

	public function fetchAllDictObject($column = null) {

		$results = [];

		while (($row = $this->fetchObject()) !== null) {

			if ($column === null)
				$column = $this->getFirstKey($row);

			$key = $row->{$column} ?? null;
			unset($row->{$column});
			
//			if (count(get_object_vars($row)) === 1)
//				$row = $this->getFirstValue($row);

			$i = 0;
			foreach ($row as $value) {
				$i ++;
				if ($i === 2)
					break;
			}
			if ($i === 1)
				$row = $value;

			$results[$key] = $row;
		}

		return $results;
	}

	public function eachArray(callable $callback) {

		while (($row = $this->fetchArray()) !== null)
			if ($callback($row, $this) === false)
				break;
	}

	public function eachAssoc(callable $callback) {

		while (($row = $this->fetchAssoc()) !== null)
			if ($callback($row, $this) === false)
				break;
	}

	public function eachObject(callable $callback) {

		while (($row = $this->fetchObject()) !== null)
			if ($callback($row, $this) === false)
				break;
	}

	public function mapArray(callable $callback) {

		$results = [];

		while (($row = $this->fetchArray()) !== null)
			$results[] = $callback($row, $this);

		return $results;
	}

	public function mapAssoc(callable $callback) {

		$results = [];

		while (($row = $this->fetchAssoc()) !== null)
			$results[] = $callback($row, $this);

		return $results;
	}

	public function mapObject(callable $callback) {

		$results = [];

		while (($row = $this->fetchObject()) !== null)
			$results[] = $callback($row, $this);

		return $results;
	}

	private function getFirstKey($row) {

		foreach ($row as $key => $value)
			return $key;

		return null;
	}

	private function getFirstValue($row) {

		foreach ($row as $key => $value)
			return $value;

		return null;
	}
}
