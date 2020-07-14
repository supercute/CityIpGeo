<?php


namespace CIPG\Core;

/**
 *
 * @author Danil Shumskikh <d@shumskikh.ru>
 */
class IpGeoBase
{
    /**
     * Путь до каталога с БД
     * @var string
     */
    private $path;
    /**
     * Ресурс полученый функцией fopen на файл с данными
     * @var resource 
     */
    private $handle;
    /**
     * Массив с мета информацией о файле с данными
     * @var array
     */
    private $meta;


    /**
     * Конструктор принимает путь к каталогу с БД и инициализирует её
     *
     * @param string $path
     * @throws \Exception
     */
    public function __construct($path)
    {
        $this->path = rtrim($path, '/');
        $this->handle = @fopen($this->path . '/db.bin', 'rb');
        
        if(!$this->handle){
            throw new \Exception('Can\'t open db');
        }
        
        fseek($this->handle, filesize($this->path . '/db.bin') - 100);
        
        $t = explode(chr(0), trim(fread($this->handle, 1024)));

        $this->meta = array(
            'countIpBlock' => $t[0],
            'countCitiesBlock' => $t[1],
            'maxLenBlockIps' => $t[2],
            'maxLenBlockCities' => $t[3],
        );
    }
    
    /**
     * Ищет информацию о IP адресе и возвращет её
     * 
     * @param string $ip IP адрес в формате 127.0.0.1
     * @return array|null
     */
    public function search($ip)
    {
        $block = $this->searchIpBlock($ip);
        
        if($block){
            
            $city = $this->searchCity($block[2]);
            $city[] = long2ip($block[0]);
            $city[] = long2ip($block[1]);
            
            return array_combine(array(
                'country',
                'city',
                'region',
                'district',
                'latitude',
                'longitude',
                'ip_start',
                'ip_stop',
            ), $city);
        }
        
        return null;
    }

    /**
     * Поиск блока с информацией о ГЕО
     * @param int $cityNum
     * @return array
     */
    private function searchCity($cityNum)
    {
        $offset = $this->meta['maxLenBlockIps'] * $this->meta['countIpBlock'] + $this->meta['maxLenBlockCities'] * (int)$cityNum - $this->meta['maxLenBlockCities'];
        fseek($this->handle, $offset);
        $buffer = rtrim(fread($this->handle, $this->meta['maxLenBlockCities']));
        
        return explode(chr(0), $buffer);
    }

    /**
     * Возвращает массив городов о которых есть информация в БД
     *
     * @return array
     */
    public function listLocations()
    {
        $offset = $this->meta['maxLenBlockIps'] * $this->meta['countIpBlock'];
        fseek($this->handle, $offset);

        $list = array();

        for($i = 1; $i <= $this->meta['countCitiesBlock']; $i++){
            $buffer = rtrim(fread($this->handle, $this->meta['maxLenBlockCities']));
            $list[] =
                array_combine(array(
                    'country',
                    'city',
                    'region',
                    'district',
                    'latitude',
                    'longitude',
                ), explode(chr(0), $buffer));
        }

        return $list;
    }


    /**
     * Поиск диапазона в который входит данный IP адрес
     *
     * @param string $ip
     * @return array|false
     */
    private function searchIpBlock($ip)
    {
        $ip = ip2long($ip);
        $first = 0;
        $last = $this->meta['countIpBlock'];

        while($first <= $last){
            $mid = $first + ceil(($last - $first) / 2);

            $block = $this->getIpBlock($mid);

            if($block[0] <= $ip AND $ip <= $block[1]){
                return $block;
            }
            elseif($block[0] >= $ip){
                $last = $mid - 1;
            }
            else{
                $first = $mid + 1;
            }
        }

        return false;
    }

    /**
     * Возвращает блок с диапазоном IP адресов основываясь на его положении в БД
     * 
     * @param int $numBlock
     * @return array
     */
    private function getIpBlock($numBlock)
    {
        $offset = $numBlock * $this->meta['maxLenBlockIps'] - $this->meta['maxLenBlockIps'];
        fseek($this->handle, $offset);
        $bufer = fread($this->handle, $this->meta['maxLenBlockIps']);
        
        $block = array();
        $t = array();
        for($i = 0; $i < 8; $i++){
            $t[] = ord($bufer{$i});
            if(count($t) == 4){
                $block[] = ip2long(implode('.', $t));
                $t = array();
            }
        }
        
        $block[] = substr($bufer, 8);
        
        return $block;
    }

    
    
    
}
