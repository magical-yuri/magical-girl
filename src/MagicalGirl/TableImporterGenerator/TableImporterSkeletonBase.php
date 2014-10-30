<?php echo '<?php' ?>

use MagicalGirl\TableImporter\TableImporter;
use MagicalGirl\ValueGenerator\RandomValueGenerator;

class <?php echo $this->baseClassName ?> extends TableImporter
{
    protected static $DATABASE_NAME = '<?php echo $this->databaseName ?>';
    protected static $TABLE_NAME = '<?php echo $this->tableName ?>';
    protected static $PRIMARY_KEY = array(
        <?php echo $this->getStringArrayElements($pkeys, 2) ?>

    );
<?php if (isset($autoincrementField)) : ?>
    protected static $AUTO_INCREMENT_FIELD = '<?php echo $autoincrementField ?>';
<?php endif ?>
    protected static $FIELDS = array(
        <?php echo $this->getStringArrayElements(array_keys($fields), 2) ?>

    );

<?php foreach ($fields as $field) : ?>
    protected function <?php echo $this->getValueGenerateMethod($field['Field']) ?>()
    {
        return <?php echo $this->getRandomValueGenerateMethod($field['Type'], $field['Key']) ?>;
    }

<?php endforeach ?>
}
