<?php

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 * @property integer $file_id
 * @property string $file_name
 * @property string $file_mime
 *
 * The followings are the available model relations:
 * @property TaskFile[] $taskFiles
 */
class file extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return file the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('file_name', 'required'),
			array('file_name', 'length', 'max'=>255),
			array('file_mime', 'length', 'max'=>63),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('file_id, file_name, file_mime', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'taskFiles' => array(self::HAS_MANY, 'TaskFile', 'task_file_file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'file_id' => 'File',
			'file_name' => 'File Name',
			'file_mime' => 'File Mime',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('file_id',$this->file_id);
		$criteria->compare('file_name',$this->file_name,true);
		$criteria->compare('file_mime',$this->file_mime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}