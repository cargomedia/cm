<?php

class CM_Db_Schema_ColumnTest extends CMTest_TestCase {

	/**
	 * @var CM_Db_Schema_Column
	 */
	private static $_columnId, $_columnIntNoSize, $_columnIntWithDefault, $_columnVarchar, $_columnTextNoSize, $_columnEnum, $_columnEnumWithDefault;

	public static function setUpBeforeClass() {
		CM_Db_Db::exec('
			CREATE TABLE `cm_db_schema_columntest` (
				`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
				`intNoSize` int unsigned NOT NULL,
				`intWithDefault` int default 100,
				`varchar` varchar(200) NOT NULL,
				`textNoSize` text,
				`enum` ENUM (\'Ar,ies\',\'Tau\\\'rus\',\'Ge mini\',\'Cancer\',\'Leo\',\'Virgo\',\'Libra\',\'Scorpio\',\'Sagittarius\',\'Capricorn\',\'Aquarius\',\'Pisces\'),
				`enumWithDefault` ENUM(\'yes\', \'no\') default \'no\',
				PRIMARY KEY (`id`)
			)');
		self::$_columnId = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'id');
		self::$_columnIntNoSize = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'intNoSize');
		self::$_columnIntWithDefault = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'intWithDefault');
		self::$_columnVarchar = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'varchar');
		self::$_columnTextNoSize = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'textNoSize');
		self::$_columnEnum = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'enum');
		self::$_columnEnumWithDefault = new CM_Db_Schema_Column(CMTest_TH::getDbClient(), 'cm_db_schema_columntest', 'enumWithDefault');
	}

	public function testSize() {
		$this->assertSame(12, self::$_columnId->getSize());
		$this->assertSame(10, self::$_columnIntNoSize->getSize()); // Default int size
		$this->assertSame(200, self::$_columnVarchar->getSize());
		$this->assertNull(self::$_columnTextNoSize->getSize());
		$this->assertNull(self::$_columnEnum->getSize());
	}

	public function testEnum() {
		$this->assertNull(self::$_columnId->getEnum());
		$this->assertNull(self::$_columnIntNoSize->getEnum());
		$this->assertNull(self::$_columnVarchar->getEnum());
		$this->assertNull(self::$_columnTextNoSize->getEnum());
		$this->assertSame(array('Ar,ies', 'Tau\'rus', 'Ge mini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius',
			'Pisces'), self::$_columnEnum->getEnum());
	}

	public function testType() {
		$this->assertSame('int', self::$_columnId->getType());
		$this->assertSame('int', self::$_columnIntNoSize->getType());
		$this->assertSame('varchar', self::$_columnVarchar->getType());
		$this->assertSame('text', self::$_columnTextNoSize->getType());
		$this->assertSame('enum', self::$_columnEnum->getType());
	}

	public function testAllowNull() {
		$this->assertFalse(self::$_columnId->getAllowNull());
		$this->assertFalse(self::$_columnIntNoSize->getAllowNull());
		$this->assertFalse(self::$_columnVarchar->getAllowNull());
		$this->assertTrue(self::$_columnTextNoSize->getAllowNull());
		$this->assertTrue(self::$_columnEnum->getAllowNull());
	}

	public function testDefaultValue() {
		$this->assertNull(self::$_columnId->getDefaultValue());
		$this->assertNull(self::$_columnIntNoSize->getDefaultValue());
		$this->assertSame('100', self::$_columnIntWithDefault->getDefaultValue());
		$this->assertNull(self::$_columnVarchar->getDefaultValue());
		$this->assertNull(self::$_columnTextNoSize->getDefaultValue());
		$this->assertNull(self::$_columnEnum->getDefaultValue());
		$this->assertSame('no', self::$_columnEnumWithDefault->getDefaultValue());
	}

	public function testUnsigned() {
		$this->assertTrue(self::$_columnId->getUnsigned());
		$this->assertTrue(self::$_columnIntNoSize->getUnsigned());
		$this->assertFalse(self::$_columnIntWithDefault->getUnsigned());
		$this->assertFalse(self::$_columnVarchar->getUnsigned());
		$this->assertFalse(self::$_columnTextNoSize->getUnsigned());
		$this->assertFalse(self::$_columnEnum->getUnsigned());
		$this->assertFalse(self::$_columnEnumWithDefault->getUnsigned());
	}

	public function testName() {
		$this->assertSame('id', self::$_columnId->getName());
		$this->assertSame('intNoSize', self::$_columnIntNoSize->getName());
		$this->assertSame('intWithDefault', self::$_columnIntWithDefault->getName());
		$this->assertSame('varchar', self::$_columnVarchar->getName());
		$this->assertSame('textNoSize', self::$_columnTextNoSize->getName());
		$this->assertSame('enum', self::$_columnEnum->getName());
		$this->assertSame('enumWithDefault', self::$_columnEnumWithDefault->getName());
	}
}
