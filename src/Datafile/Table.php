<?php
namespace Zaek\Framy\Datafile;

class Table
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    private $table = '';

    /**
     * @var resource
     */
    private $resource = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $meta = [];

    /**
     * Table constructor.
     * @param Database $db
     * @param $table
     */
    public function __construct(Database $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Выборка данных
     *
     * @param array $aFilter
     * @param array $aRange
     * @param array $aOrder
     * @return Dataset
     * @throws ColumnCountMismatch
     * @throws InvalidField
     */
    public function select($aFilter = [], $aRange = [], $aOrder = [])
    {
        $dataset = new Dataset();
        if(empty($aRange)) {
            $aRange = array_keys($this->data[0]);
        }
        $dataset->setNames($aRange);
        foreach($this->data as $row) {
            foreach($aFilter as $k => $v) {
                if(!isset($row[$k])) {
                    throw new InvalidField;
                }
                if($row[$k] !== $v) {
                    continue 2;
                }
            }

            $rowAdd = [];
            foreach($aRange as $item) {
                $rowAdd[$item] = $row[$item];
            }

            $dataset->addRow($rowAdd);
        }

        $dataset->sort($aOrder);

        return $dataset;
    }
    /**
     * Добавление строки
     *
     * @param $aData
     * @return mixed
     */
    public function insert($aData)
    {
        $aData['_id'] = $this->meta['id']+1;
        $aData['_created'] = microtime(true);
        $aData['_updated'] = $aData['_created'];

        $this->data[] = $aData;
        if($this->save()) {
            return $aData['_id'];
        } else {
            return false;
        }
    }
    /**
     * Добавление нескольких строк
     *
     * @param $aKeys
     * @param $aData
     * @return mixed
     */
    public function insertMultiple($aKeys, $aData)
    {
        foreach ( $aData as $line ) {
            $this->insert(array_combine($aKeys, $line));
        }

        return true;
    }
    /**
     * Удаление строк
     *
     * @param array $aFilter
     * @param array $aOrder ['key' => SORT_ASC|SORT_DESC]
     * @param array $aLimit [10]|[10,5]|10
     * @return mixed
     */
    public function delete($aFilter = [], $aOrder = [], $aLimit = [])
    {
        $toDelete = $this->prepareData($this->data, $aFilter, $aOrder, $aLimit);

        foreach ($toDelete as $item) {
            unset($this->data[$item]);
        }

        $this->data = array_values($this->data);
        return $this->save();
    }

    private function prepareData($data, $aFilter, $aOrder, $aLimit)
    {
        $indexes = [];

        foreach($data as $index => $row) {
            foreach($aFilter as $k => $v) {
                if($row[$k] !== $v) {
                    continue 2;
                }
            }

            $indexes[] = $index;
        }

        if($aLimit) {
            $data = array_filter($data, function($key) use($indexes) {
                return in_array($key, $indexes);
            }, ARRAY_FILTER_USE_KEY);

            uasort($data, function($a, $b) use ($aOrder) {
                foreach($aOrder as $field => $dir) {
                    $result = strcmp($a[$field], $b[$field]);
                    if($result != 0) {
                        return $dir == SORT_ASC ? $result : $result * -1;
                    }
                }

                return 0;
            });

            if(!is_array($aLimit)) {
                $indexes = array_slice($indexes, 0, $aLimit);
            } else if (count($aLimit) == 1) {
                $indexes = array_slice($indexes, 0, $aLimit[0]);
            } else if (count($aLimit) == 2) {
                $indexes = array_slice($indexes, $aLimit[0], $aLimit[1]);
            } else {
                throw new \InvalidArgumentException;
            }
        }

        return $indexes;
    }

    /**
     * Обновление строк
     *
     * @param $aUpdate
     * @param array $aFilter
     * @param array $aOrder
     * @param array $aLimit
     * @return mixed
     */
    public function update($aUpdate, $aFilter = [], $aOrder = [], $aLimit = [])
    {
        $toUpdate = $this->prepareData($this->data, $aFilter, $aOrder, $aLimit);

        foreach($toUpdate as $index) {
            foreach($aUpdate as $k => $v) {
                $this->data[$index][$k] = $v;
            }
            $this->data[$index]['_updated'] = microtime(true);
        }

        return $this->save();
    }

    /**
     * @return mixed
     * @throws ErrorOpenTable
     */
    public function open()
    {
        if(is_null($this->resource)) {
            $filename = $this->getFile();
            if(file_exists($filename)) {
                $this->resource = fopen($filename, 'r+');
            }

            if(!$this->resource) {
                throw new ErrorOpenTable;
            }
        }

        return $this->resource;
    }
    /**
     * @return bool
     */
    public function lock()
    {
        if($this->resource) {
            return flock($this->resource, LOCK_EX);
        }

        return false;
    }
    /**
     * @return bool
     */
    public function release()
    {
        if($this->resource) {
            return flock($this->resource, LOCK_UN);
        }

        return true;
    }
    /**
     * @return bool
     */
    public function close()
    {
        if(!is_null($this->resource)) {
            $this->release();
            fclose($this->resource);
        }

        return true;
    }
    /**
     * @return bool
     */
    public function read()
    {
        $data = fread($this->resource, filesize($this->getFile()));
        $data = json_decode($data, true);

        $this->data = $data['data'];
        $this->meta = $data['meta'];

        unset($data);
        return true;
    }
    /**
     * @return bool
     */
    public function save()
    {
        ftruncate($this->resource, 0);
        rewind($this->resource);
        fwrite($this->resource, json_encode([
            'data' => $this->data,
            'meta' => $this->meta,
        ]));
        fflush($this->resource);

        return true;
    }
    /**
     * @return string
     */
    protected function getFile()
    {
        $dir = $this->db->getDataDirectory();
        $filename = $dir . '/' . $this->table . '.json';
        return $filename;
    }
    /**
     * @return bool|int
     */
    public function create()
    {
        if(!file_exists($this->getFile())) {
            return file_put_contents($this->getFile(), json_encode([
                'meta' => [
                    'created_at' => microtime(true),
                    'updated_at' => microtime(true),
                    'id' => 1,
                ],
                'data' => [],
            ]));
        }

        return true;
    }
}