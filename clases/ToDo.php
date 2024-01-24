<?php

class ToDo
{
    /**
     * @var string
     */
    private string $basePath = APP_DIR . 'store' . '/';
    /**
     * @var string
     */
    private string $filePath;
    /**
     * @var array
     */
    private array $list = [];

    /**
     * @param string $listName
     * @throws Exception
     */
    public function __construct(string $listName)
    {
        $this->setFilePath($listName);
        $filePath = $this->getFilePath();
        $this->createFile($filePath);
        $this->getData($filePath);
    }

    /**
     * @param string $taskName
     * @param int $priority
     * @return void
     * @throws Exception
     */
    public function addTask(string $taskName, int $priority): void //add task to list
    {
        $task = $this->createTask($taskName, $priority);
        $list = $this->getList();
        $list[$task['id']] = $task;
        $this->setList($list);
    }

    /**
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function deleteTask(string $id): void //delete task from list
    {
        $this->unsetTask($id);
    }

    /**
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function completeTask(string $id): void //set task status to 'completed'
    {
        $list = $this->getList();
        $this->validateTaskId($id, $list);
        $this->toggleTask($id, $list);
        $this->setList($list);
    }

    /**
     * @return array
     */
    public function getTasks(): array //return all tasks from list
    {
        $list = $this->getList();
        if (!empty($list)) {
            $this->sortTasks($list);
        }
        return $list;
    }


    //Task methods

    /**
     * @param string $taskName
     * @param int $priority
     * @return void
     * @throws Exception
     */
    private function validateParams(string $taskName, int $priority): void //validate accepted task parameters
    {
        if (!trim($taskName)) {
            throw new Exception('Task can\'t be empty');
        }

        if ($priority <= 0 || $priority > 10) {
            throw new Exception('Priority mast be in range from 1 to 10');
        }
    }

    /**
     * @param string $taskName
     * @param int $priority
     * @return array
     * @throws Exception
     */
    private function createTask(string $taskName, int $priority): array //create task
    {
        $this->validateParams($taskName, $priority);
        $id = uniqid();
        $status = Status::NOTDONE;
        return [
            'id' => $id,
            'name' => $taskName,
            'priority' => $priority,
            'status' => $status->value,
        ];
    }

    /**
     * @param string $id
     * @return void
     * @throws Exception
     */
    private function unsetTask(string $id): void  //unset task from list
    {
        $list = $this->getList();
        $this->validateTaskId($id, $list);
        $list = $this->getList();
        unset($list[$id]);
        $this->setList($list);
    }

    /**
     * @param string $id
     * @param $list
     * @return void
     */
    private function toggleTask(string $id, &$list): void //change status
    {
        if ($list[$id]['status'] === Status::NOTDONE->value) {
            $list[$id]['status'] = Status::DONE->value;
        }
    }

    //List methods

    /**
     * @return array
     */
    private function getList(): array // get list
    {
        return $this->list;
    }

    /**
     * @param $list
     * @return void
     */
    private function setList($list): void //set list
    {
        $this->list = $list;
    }

    //File methods

    /**
     * @param $filePath
     * @return void
     * @throws Exception
     */
    private function createFile($filePath): void //create file for writing tasks between session
    {
        if (file_exists($filePath)) {
            return;
        }
        $file = fopen($filePath, 'w');
        if (!$file) {
            throw new Exception('Can\'t create file.');
        }
        fclose($file);
    }

    /**
     * @param string $filePath
     * @return void
     */
    private function getData(string $filePath): void //get data from file when session starts
    {
        $list = [];
        $data = file($filePath);
        foreach ($data as $str) {
            $task = $this->convertToArrayFromString($str);
            if (!$task) {
                continue;
            }
            $list[$task['id']] = $task;
        }
        $this->setList($list);

    }

    /**
     * @param string $filePath
     * @param array $list
     * @return void
     */
    private function setData(string $filePath, array $list): void //set data to file when session end
    {
        file_put_contents($filePath, '');
        foreach ($list as $id => $task) {
            $expression = $id . '|' . $task['name'] . '|' . $task['priority'] . '|' . $task['status'];
            $this->writeTask($filePath, $expression);
        }
    }

    /**
     * @param string $filePath
     * @param string $data
     * @return void
     */
    private function writeTask(string $filePath, string $data): void //write tasks to file
    {
        file_put_contents($filePath, $data . PHP_EOL, FILE_APPEND);
    }

    //Helpers
    /**
     * @throws Exception
     */
    private function validateTaskId(string $id, $list): void //validate task id in list
    {

        if (!array_key_exists($id, $list)) {
            throw new Exception('There is no such tusk');
        }
    }

    /**
     * @param array $array
     * @return void
     */
    private function sortTasks(array &$array): void //sort tasks in descending order
    {
        usort($array, function ($a, $b) {
            if ($a['priority'] > $b['priority']) {
                return -1;
            } elseif ($a['priority'] < $b['priority']) {
                return 1;
            }
            return 0;
        });
    }

    /**
     * @param string $string
     * @return array|null
     */
    private function convertToArrayFromString(string $string): array|null //convert tasks from string to array
    {
        $str = trim($string);
        if (!$str) {
            return null;
        }
        [$id, $name, $priority, $status] = explode('|', $str);
        return [
            'id' => $id,
            'name' => $name,
            'priority' => $priority,
            'status' => $status
        ];
    }

    //File path methods

    /**
     * @return string
     */
    private function getBasePath(): string // get base path
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    private function getFilePath(): string //get file path
    {
        return $this->filePath;
    }

    /**
     * @param string $listName
     * @return void
     */
    private function setFilePath(string $listName): void //set file path
    {
        $basePath = $this->getBasePath();
        $this->filePath = $basePath . $listName . '.' . 'txt';
    }

    public function __destruct()
    {
        $filePath = $this->getFilePath();
        $list = $this->getList();
        $this->setData($filePath, $list);
    }
}