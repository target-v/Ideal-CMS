<?php
namespace Ideal\Structure\Part\Site;

use Ideal\Core\Db;
use Ideal\Core\Site;
use Ideal\Core\Config;
use Ideal\Core\Util;
use Ideal\Field;
use Ideal\Field\Url;

class ModelAbstract extends Site\Model
{

    /**
     * @param int $page Номер отображаемой страницы
     * @param int $onPage Кол-во элементов на странице
     * @return array Полученный список элементов
     */
    public function getList($page)
    {
        $list = parent::getList($page);

        // Построение правильных URL
        $url = new Field\Url\Model();
        $url->setParentUrl($this->path);
        if (is_array($list) and count($list) != 0 ) {
            foreach ($list as $k => $v) {
                $list[$k]['link'] = $url->getUrl($v);
            }
        }

        return $list;
    }


    /**
     * @param $where
     * @return string
     */
    protected function getWhere($where)
    {
        // Считываем все элементы первого уровня
        $lvl = 1;
        $cid = '';

        if (count($this->path) > 0) {
            // Считываем все элементы последнего уровня из пути
            $c = count($this->path);
            $end = end($this->path);
            if ($end['is_self_menu']) return false;
            if (isset($this->path[$c - 2]) AND ($end['structure'] == $this->path[$c - 2]['structure'])) {
                $lvl = $end['lvl'] + 1;
                $cidModel = new Field\Cid\Model($this->params['levels'], $this->params['digits']);
                $cid = $cidModel->getCidByLevel($end['cid'], $end['lvl'], false);
                $cid = " AND cid LIKE '{$cid}%'";
            }
        }

        if (is_array($end) && $end['is_self_menu'] == 0) {
            $where .= "AND lvl={$lvl} {$cid} AND is_active=1 AND is_not_menu=0";
        }

        if ($where != '') {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }


    public function detectPageByUrl($path, $url)
    {
        $db = Db::getInstance();

        // составляем запрос из списка URL
        $_sql = ' is_skip=1';
        foreach ($url as $v) {
            if ($v == '') continue;
            $_sql .= ' OR url="' . mysql_real_escape_string($v) . '"';
        }

        $_sql = "SELECT * FROM {$this->_table} WHERE ({$_sql})
                    AND prev_structure='{$this->prevStructure}' AND is_active=1 ORDER BY lvl, cid";

        $list = $db->queryArray($_sql); // запрос на получение всех страниц, соответствующих частям url

        // Страницу не нашли устанавливаем флаг 404-ошибки
        if (!isset($list[0]['cid'])) {
            $this->path = $path;
            $this->is404 = true;
            return $this;
        }

        $cidModel = new Field\Cid\Model($this->params['levels'], $this->params['digits']);

        // Убираем ненужные элементы с теми же URL, но из других ветвей

        // Распределяем считанные cid'ы по веткам
        $branches = array();
        foreach($list as $v) {
            if ($v['lvl'] == 1) {
                $cid = $v['cid'];
                $branches[$cid]['count'] = 1;
                $branches[$cid]['branch'][] = $v;
            } else {
                $cid = $cidModel->getCidByLevel($v['cid'], $v['lvl'] - 1);
                if (!isset($branches[$cid]['count'])) continue;
                $newCid = $v['cid'];
                $branches[$newCid] = $branches[$cid];
                $branches[$newCid]['count']++;
                $branches[$newCid]['branch'][] = $v;
            }
        }

        // Сортируем ветки по количеству элементов (по убыванию)
        usort($branches, function($a, $b){
            return ($b['count'] - $a['count']);
        });

        // Проходим каждую ветку, начиная с наибольшей, пока не найдём полный путь
        // без разрывов или не кончатся ветки
        $newPath = array();
        foreach ($branches as $branch) {
            $isOk = true;
            foreach ($branch['branch'] as $k => $v) {
                if (($k+1) != $v['lvl']) {
                    $isOk = false;
                    break;
                }
            }
            if ($isOk) {
                $end = end($branch['branch']);
                if ($end['is_skip'] == 1) {
                    // Последний элемент в цепочке не может быть пропущенным
                    continue;
                }
                $newPath = $branch['branch'];
                break;
            }
        }

        if (count($newPath) == 0) {
            $this->path = $path;
            $this->is404 = true;
            return $this;
        }

        $this->path = array_merge($path, $newPath);

        // Подсчитываем кол-во элементов пути, без учёта пропущенных сегментов
        // и составляем строку найденной части URL
        $count = 0;
        $parsedUrl = $sep = '';
        foreach($newPath as $v) {
            if ($v['is_skip'] == 0) {
                $parsedUrl .= $sep . $v['url'];
                $sep = '/';
                $count++;
            }
        }

        // Вырезаем из переданного URL найденное количество сегментов и склеиваем их в строку
        $parsedUrlPart = array_slice($url, 0, $count);
        $parsedUrlPart = implode('/', $parsedUrlPart);
        if ($parsedUrl != $parsedUrlPart) {
            $this->is404 = true;
            return $this;
        }

        $url = array_slice($url, $count);
        if (count($url) > 0) {
            // Остались неразобранные сегменты URL, запускаем вложенную структуру
            // Определяем оставшиеся элементы пути
            $end = end($this->path);
            $config = Config::getInstance();
            $rootStructure = $config->getStructureByPrev($end['prev_structure']);
            $modelClassName = Util::getClassName($end['structure'], 'Structure') . '\\Site\\Model';
            /* @var $structure Model */
            $structure = new $modelClassName($rootStructure['ID'] . '-' . $end['ID']);

            // Запускаем определение пути и активной модели по $par
            $model = $structure->detectPageByUrl($this->path, $url);
            return $model;
        } else {
            // Неразобранных сегментов не осталось, возвращаем в качестве модели сам объект
            return $this;
        }
    }


    public function getStructureElements()
    {
        $db = Db::getInstance();
        $config = Config::getInstance();
        $urlModel = new Url\Model();

        $_sql = "SELECT * FROM {$this->_table} WHERE is_active=1 ORDER BY cid";
        $list = $db->queryArray($_sql);

        if (count($this->path) == 0 ) {
            $url = array('0' => array('url' => $config->structures[0]['url']));
        } else {
            $url = $this->path;
        }

        $lvl = 0;
        foreach ($list as $k => $v) {
            if ($v['lvl'] > $lvl) {
                if (($v['url'] != '/') AND ($k > 0)) {
                    $url[] = $list[$k-1];
                }
                $urlModel->setParentUrl($url);
            } elseif ($v['lvl'] < $lvl) {
                // Если двойной или тройной выход добавляем соответствующий мультипликатор
                $c = $lvl - $v['lvl'];
                $url = array_slice($url, 0, -$c);
                $urlModel->setParentUrl($url);
            }
            $lvl = $v['lvl'];
            $list[$k]['link'] = $urlModel->getUrl($v);
        }
        return $list;
    }

    /**
     * Построение пути в рамках одной структуры.
     */
    public function getLocalPath()
    {
        $category = $this->pageData;

        if ($category['lvl'] == 1) {
            // Если в локальной структуре родителей нет, возвращаем сам объект
            return array($category);
        }

        // По cid определяем cid'ы всех родителей
        $cid = new \Ideal\Field\Cid\Model($this->params['levels'], $this->params['digits']);
        $cids = $cid->getParents($category['cid']);

        $path = array();
        if (count($cids) > 0) {
            // Выстраиваем строку cid'ов для запроса в БД
            $strCids = $separator = '';
            foreach ($cids as $v) {
                $strCids .= $separator . "'" . $v . "'";
                $separator = ', ';
            }

            // Считываем все элементы с указанными cid'ами
            $db = Db::getInstance();
            $_sql = "SELECT * FROM {$this->_table} WHERE cid IN ({$strCids}) ORDER BY cid";
            $path = $db->queryArray($_sql);
        }

        $path = array_merge($path, array($category)); // добавляем наш элемент к родительским

        return $path;
    }

}