<?php
/**
 * Сравнение двух текстов на похожесть по алгоритму шинглов
 * @author C4 studio <c4@day.ua>
 * @copyright 2009 C4 studio
 * @package C4MS
 * @subpackage classes
 */ 
class Shingles
{
    /**
     * Массив параметров
     *
     * @var array
     */
 
    protected $_params = array();
 
    /**
     * Массив стоп-символов, которые будут удаленны из строки
     *
     * @var array
     */
    protected $_stopSymbols = array();
 
    /**
     * Стоп-символы по умолчанию
     *
     * @var array
     */
    protected $_defaultStopSymbols = "x27 x22 x60 \t \n \r ' , . / « » # ; : @ ~ [ ] { } = - + ) ( * & ^ % $ < > ? !";
    
    /**
     * Массив стоп-слов, которые будут удаленны из строки
     *
     * @var array
     */
    protected $_stopWords = array();
    
    /**
     * Массив стоп-слов, по умолчанию
     *
     * @var array
     */
    protected $_defaultStopWords = "а, без, более, бы, был, была, были, было, быть, в, вам, вас, весь, во, вот, все, всего, всех, вы, где, да, даже, для, до, его, ее, если, есть, ещё, же, за, здесь, и, из, из-за, или, им, их, к, как, как-то, ко, когда, кто, ли, либо, мне, может, мы, на, надо, наш, не, него, неё, нет, ни, них, но, ну, о, об, однако, он, она, они, оно, от, очень, по, под, при, с, со, так, также, такой, там, те, тем, то, того, тоже, той, только, том, ты, у, уже, хотя, чего, чей, чем, что, чтобы, чьё, чья, эта, эти, это, я";
 
    /**
     * Массив текстов для обработки
     *
     * @var array
     */
 
    protected $_texts = array();
 
    /**
     * Двухмерный массив шинглов
     *
     * @var array
     */
 
    protected $_shingles = array();
 
    /**
     * Метод выполняющий канонизацию строки. Сначала вырезаем из строки все
     * стоп-символы. Затем перебираем слова. Те которых нет в массиве стоп-слов,
     * оставляем. Приводим строку к нижнему регистру.
     *
     * @param string $string строка для канонизации
     * @return string "чистую" строку
     */
 
    protected function _canonizeString($string)
    {
        $result = array();
        $string = str_replace($this->_stopSymbols, ' ', $string);
 
        foreach (explode(' ', $string) as $word) {
            if (strlen($word) && !in_array($word, $this->_stopWords)) {
              $result[] = trim($word);
            }
        }
 
        return strtolower(implode(' ', $result));
    }
 
    /**
     * Выполняет канонизацию всех добавленных для обработки текстов
     *
     * @return object текущий объект
     */
 
    protected function _canonizeTexts()
    {
        if (empty($this->_texts)) {
          return $this;
        }
 
        foreach ($this->_texts as $key => $text) {
            $this->_texts[$key] = $this->_canonizeString($text);
        }
 
        return $this;
    }
 
    /**
     * Создает массив шинглов строки
     *
     * @param string $string строка для обработки
     * @return array массив шиглов
     */
 
    protected function _getShinglesFromString($string)
    {
        if (!$this->getParam('length')) {
            throw new Exception('Не указана длина шингла');
        }
 
        $shingles = array();
        $length   = intval($this->getParam('length'));
        $words    = explode(' ', $string);

        /* 
         * если длина входного текста меньше длины шингла - "закольцевать" входной текст до 
         * нужной длины 
         * (не криво?)
         */
        if (count($words) < $length + 1) {
          /*$wordIndex = -1;
          $wordSize = count($words);
          for($i = $wordSize; $i < $length * 10; $i++){
            if(++$wordIndex >= $wordSize)
              $wordIndex = 0;
            $words[] = $words[$wordIndex];
          }
          var_dump($words);*/
          return $shingles;
        }
 
        for ($i = 1; $i <= count($words) - $length; $i ++) {
            $shingles[] = crc32(implode(' ', array_slice($words, $i, $length)));
        }
 
        return $shingles;
    }
 
    /**
     * Создает массив шинглов для каждого текста
     *
     * @return array
     */
 
    protected function _setShingles()
    {
        foreach ($this->_texts as $text) {
            $this->_shingles[] = $this->_getShinglesFromString($text);
        }
 
        return $this;
    }
 
    /**
     * Конструктор класса
     *
     * @param array $setting параметры для обработки текстов
     */
 
    public function __construct($shingleLength, $stopSymbols = null, $stopWords = null)
    {
      $stopSymbols = empty($stopSymbols) ? $this->_defaultStopSymbols : $stopSymbols;
      $this->setStopSymbols(explode(' ', $stopSymbols));

      $stopWords = empty($stopWords) ? $this->_defaultStopWords : $stopWords;
      $this->setStopWords(explode(', ', $stopWords));
 
      $this->setParam('length', $shingleLength);
    }
 
    /**
     * Определяет массив стоп-символов
     *
     * @param array $symbols массив стоп-символов
     * @return object текущий объект
     */
 
    public function setStopSymbols(array $symbols)
    {
        $this->_stopSymbols = $symbols;
        return $this;
    }
 
    /**
     * Возвращает массив стоп-символов
     *
     * @return array массив стоп-символов
     */
 
    public function getStopSymbols()
    {
        return $this->_stopSymbols;
    }
 
    /**
     * Определяет массив стоп-слов
     *
     * @param array $words стоп-слова
     * @return object текущий объект
     */
 
    public function setStopWords(array $words)
    {
        $this->_stopWords = $words;
        return $this;
    }
 
    /**
     * Возвращает массив стоп-слов
     *
     * @return array массив стоп-слов
     */
 
    public function getStopWords()
    {
        return $this->_stopWords;
    }
 
    /**
     * Возвращает параметр
     *
     * @param string $key ключ
     * @return mixed|null значение
     */
 
    public function getParam($key)
    {
        if (isset($this->_params[$key])) {
          return $this->_params[$key];
        }
 
        return null;
    }
 
    /**
     * Возвращает массив всех параметров
     *
     * @return array массив всех параметров
     */
 
    public function getParams()
    {
        return $this->_params;
    }
 
    /**
     * Определяет массив параметров
     *
     * @param array $params массив параметров
     * @return object текущий объект
     */
 
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }
 
    /**
     * Определяет параметр
     *
     * @param string $key ключ параметра
     * @param mixed $value значение параметра
     * @return object текущий объект
     */
 
    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;
        return $this;
    }
 
    /**
     * Добавляет текст для проверки
     *
     * @param string $text текст
     * @return object текущий объект
     */
 
    public function addText($text)
    {
        $this->_texts[] = strip_tags($text);
        return $this;
    }
 
    /**
     * Проверяет двухмерный массив шинглов на уникальность
     *
     * @param array $shingles массив шинглов
     * @return int процент уникальности
     */
 
    public function compare(array $shingles1, array $shingles2)
    {
      if(empty($shingles1))
        return -1;
      
      if(empty($shingles2))
        return -2;
      
        $same = 0;
        
        foreach ($shingles1 as $shingle1) 
          foreach($shingles2 as $shingleN)
            if ($shingle1 === $shingleN) 
              $same ++;

        return $same / count($shingles1) * 100;
    }
 
    /**
     * Возвращает массив шинглов для всех добавленных текстов
     *
     * @return array массив шинглов
     */
 
    public function getShigles()
    {
      $this->_canonizeTexts();
      $this->_setShingles();
      return $this->_shingles;
    }
}
