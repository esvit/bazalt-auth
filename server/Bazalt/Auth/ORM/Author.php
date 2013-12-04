<?php
/**
 * Author.php
 *
 */
namespace Bazalt\Auth\ORM;

/**
 * Author
 * Auto add info about current user before insert/update ORM record
 *
 */
class Author extends \Bazalt\ORM\Plugin\AbstractPlugin
{
    /**
     * Ініціалізує плагін
     *
     * @param \Bazalt\ORM\Record $model   Модель, для якої викликано init
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    public function init(\Bazalt\ORM\Record $model, $options)
    {
        \Bazalt\ORM\BaseRecord::registerEvent($model->getModelName(), \Bazalt\ORM\BaseRecord::ON_RECORD_SAVE, array($this,'onSave'));
    }

    /**
     * Додає додаткові службові поля до моделі.
     * Викликається в момент ініціалізації моделі
     *
     * @param \Bazalt\ORM\Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    protected function initFields(\Bazalt\ORM\Record $model, $options)
    {
        $columns = $model->getColumns();
        if (array_key_exists('created_by', $options) && !array_key_exists($options['created_by'], $columns)) {
            $model->hasColumn($options['created_by'], 'UN:int(10)');
        }
        if (array_key_exists('updated_by', $options) && !array_key_exists($options['updated_by'], $columns)) {
            $model->hasColumn($options['updated_by'], 'UN:int(10)');
        }
    }

    /**
     * Хендлер на евент моделі onSave. Викликається при збереженні об'єкта в БД
     *
     * @param \Bazalt\ORM\Record $record  Поточний запис
     * @param bool       &$return Флаг, який зупиняє подальше виконання save()
     *
     * @return void
     */
    public function onSave(\Bazalt\ORM\Record $record, &$return)
    {
        $options = $this->getOptions();
        $user = \Bazalt\Auth::getUser();
        if (!array_key_exists(get_class($record), $options) || $user->isGuest()) {
            return;
        }
        $options = $options[get_class($record)];
        if (array_key_exists('created_by', $options) && $record->isPKEmpty()) {
            $record->{$options['created_by']} = $user->id;
        }
        if (array_key_exists('updated_by', $options)) {
            $record->{$options['updated_by']} = $user->id;
        }
    }
}