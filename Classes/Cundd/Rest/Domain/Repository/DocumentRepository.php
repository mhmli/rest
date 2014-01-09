<?php
namespace Cundd\Rest\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andreas Thurnheer-Meier <tma@iresults.li>, iresults
 *  Daniel Corn <cod@iresults.li>, iresults
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Exception\InvalidDatabaseNameException;
use Cundd\Rest\Domain\Exception\NoDatabaseSelectedException;
use Iresults\Core\Iresults;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 *
 *
 * @package rest
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DocumentRepository extends Repository {
	/**
	 * Currently selected database
	 *
	 * @var string
	 */
	protected $database;

	/**
	 * Selects a database
	 *
	 * @param string $database
	 * @throws InvalidDatabaseNameException if an invalid database name is provided
	 */
	public function setDatabase($database) {
		if (!ctype_alnum($database)) throw new InvalidDatabaseNameException('The given database name is invalid', 1389258923);
		$this->database = $database;
	}

	/**
	 * Returns the currently selected database
	 *
	 * @return string
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * Gets/sets the current database
	 *
	 * @return string
	 */
	public function database() {
		if (func_num_args() > 0) {
			$this->setDatabase(func_get_arg(0));
		}
		return $this->getDatabase();
	}

	/**
	 * Adds an object to this repository
	 *
	 * @param Document $object The object to add
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function add($object) {
		if (!$object->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$object->_setDb($currentDatabase);
		}
		parent::add($object);
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param Document $object The object to remove
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function remove($object) {
		if (!$object->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$object->_setDb($currentDatabase);
		}
		parent::remove($object);
	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param Document $modifiedObject The modified object
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function update($modifiedObject) {
		if (!$modifiedObject->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$modifiedObject->_setDb($currentDatabase);
		}
		parent::update($modifiedObject);
	}

	/**
	 * Returns all objects of the selected Document database
	 *
	 * @throws NoDatabaseSelectedException if no database has been selected
	 * @return array<Document>
	 * @api
	 */
	public function findAll() {
		$currentDatabase = $this->getDatabase();
		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);

		$query = $this->createQuery();
		$query->matching($query->equals('db', $currentDatabase));
		return $this->convertResults($query->execute());
	}

	/**
	 * Returns all objects of the given Document database
	 *
	 * Will select the given Document database and call findAll()
	 *
	 * @param string $database
	 * @return array<Document>
	 */
	public function findByDatabase($database) {
		$this->setDatabase($database);
		return $this->findAll();
	}

//	/**
//	 * Returns the Document with the given GUID
//	 *
//	 * @param string $guid
//	 * @return Document
//	 */
//	public function findByGuid($guid) {
//		$query = $this->createQuery();
//		$query->matching($query->equals('guid', $guid));
//		$query->setLimit(1);
//		return $this->convertResult($query->execute());
//	}

	/**
	 * Returns the Document with the given ID
	 *
	 * @param string $id
	 * @return Document
	 */
	public function findOneById($id) {
		$query = $this->createQuery();
		$query->matching($query->equals('id', $id));
		$result = $query->execute();
		return $this->convertResult(reset($result));
	}

	/**
	 * @see findOneById()
	 */
	public function findById($id) {
		return $this->findOneById($id);
	}

	/**
	 * Returns all objects ignoring the selected database
	 *
	 * @return array<Document>
	 * @api
	 */
	public function findAllIgnoreDatabase() {
		return $this->convertResults($this->createQuery()->execute());
	}

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @throws NoDatabaseSelectedException if no database has been selected
	 * @return integer The object count
	 * @api
	 */
	public function countAll() {
		$currentDatabase = $this->getDatabase();
		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);

		$query = $this->createQuery();
		$query->matching($query->equals('db', $currentDatabase));
		return $query->execute()->count();
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll() {
		foreach ($this->findAll() AS $object) {
			$this->remove($object);
		}
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param integer $uid The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUid($uid) {
		return $this->persistenceManager->getObjectByIdentifier($uid, $this->objectType);
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param mixed $identifier The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByIdentifier($identifier) {
		return $this->persistenceManager->getObjectByIdentifier($identifier, $this->objectType);
	}

//	/**
//	 * Sets the property names to order the result by per default.
//	 * Expected like this:
//	 * array(
//	 * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
//	 * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
//	 * )
//	 *
//	 * @param array $defaultOrderings The property names to order by
//	 * @return void
//	 * @api
//	 */
//	public function setDefaultOrderings(array $defaultOrderings) {
//		$this->defaultOrderings = $defaultOrderings;
//	}
//
//	/**
//	 * Sets the default query settings to be used in this repository
//	 *
//	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
//	 * @return void
//	 * @api
//	 */
//	public function setDefaultQuerySettings(\TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings) {
//		$this->defaultQuerySettings = $defaultQuerySettings;
//	}

	/**
	 * Dispatches magic methods (findBy[Property]())
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments The arguments of the magic method
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
	 * @return mixed
	 * @api
	 */
	public function __call($methodName, $arguments) {
		// @todo: Fix me
//		$currentDatabase = $this->getDatabase();
//		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);
//
//		$query = $this->createQuery();
//		$query->matching($query->equals('db', $currentDatabase));
//
//		if (substr($methodName, 0, 6) === 'findBy' && strlen($methodName) > 7) {
//			$propertyName = lcfirst(substr($methodName, 6));
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->execute();
//			return $result;
//		} elseif (substr($methodName, 0, 9) === 'findOneBy' && strlen($methodName) > 10) {
//			$propertyName = lcfirst(substr($methodName, 9));
//
//
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->setLimit(1)->execute();
//			if ($result instanceof \TYPO3\CMS\Extbase\Persistence\QueryResultInterface) {
//				return $result->getFirst();
//			} elseif (is_array($result)) {
//				return isset($result[0]) ? $result[0] : NULL;
//			}
//
//		} elseif (substr($methodName, 0, 7) === 'countBy' && strlen($methodName) > 8) {
//			$propertyName = lcfirst(substr($methodName, 7));
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->execute()->count();
//			return $result;
//		}
		throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException('The method "' . $methodName . '" is not supported by the repository.', 1233180480);
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 * @api
	 */
	public function createQuery() {
		$query = $this->persistenceManager->createQueryForType($this->objectType);
		if ($this->defaultOrderings !== array()) {
			$query->setOrderings($this->defaultOrderings);
		}
		if ($this->defaultQuerySettings !== NULL) {
			$query->setQuerySettings(clone $this->defaultQuerySettings);
		}
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		return $query;
	}

	/**
	 * Converts the query result into objects
	 *
	 * @param array $resultCollection
	 * @return array<Document>
	 */
	protected function convertResults($resultCollection) {
		$convertedObjects = array();
		foreach ($resultCollection as $resultSet) {
			$convertedObjects[] = $this->convertResult($resultSet);
		}

		return $convertedObjects;
	}

	/**
	 * Converts the query result set into objects
	 *
	 * @param array $resultSet
	 * @return Document
	 */
	protected function convertResult($resultSet) {
		$convertedObject = new Document();
		foreach ($resultSet as $key => $value) {
			$convertedObject->setValueForKey($value, $key);
		}
		return $convertedObject;
	}


}
?>