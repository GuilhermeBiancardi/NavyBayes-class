<?php

/**
 * Biancardi
 */
class NavyBayes {
    /**
     * @var array
     */
    public $STATE_KEYS = [
        'categorias',
        'quantidadeCategorias',
        'quantidadeSentencas',
        'vocabulario',
        'tamanhoVocabulario',
        'quantidadePalavras',
        'frequenciaPalavras',
    ];
    /**
     * @var array
     */
    public $categorias;
    /**
     * @var number
     */
    public $quantidadeCategorias;
    /**
     * @var number
     */
    public $quantidadeSentencas;
    /**
     * @var array
     */
    public $vocabulario;
    /**
     * @var number
     */
    public $tamanhoVocabulario;
    /**
     * @var number
     */
    public $quantidadePalavras;
    /**
     * @var number
     */
    public $frequenciaPalavras;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var function
     */
    protected $tokenizer;
    /**
     * Initialize an instance of a Alice Classifier
     *
     * @param array $options
     */
    public function __construct($options = null) {
        // set options object
        $this->options = $options;
        $this->reset();
    }
    /**
     * Identify the category of the provided text parameter.
     *
     * @param  string $text
     * @return string the category or null
     */
    public function categorize($text) {
        $that = $this;
        $maxProbability = -INF;
        $chosenCategory = null;
        if ($that->quantidadeSentencas > 0) {
            $probabilities = $that->probabilities($text);
            // iterate thru our categorias to find the one with max probability
            // for this text
            foreach ($probabilities as $category => $logProbability) {
                if ($logProbability > $maxProbability) {
                    $maxProbability = $logProbability;
                    $chosenCategory = $category;
                }
            }
        }
        return $chosenCategory;
    }
    /**
     * Build a frequency hashmap where
     *  - the keys are the entries in `tokens`
     *  - the values are the frequency of each entry in `tokens`
     *
     * @param  array $tokens array of string
     * @return array hashmap of token frequency
     */
    public function frequencyTable($tokens) {
        $frequencyTable = [];
        // => for each category, how often were documents mapped to it
        foreach ($tokens as $token) {
            if (!isset($frequencyTable[$token])) {
                $frequencyTable[$token] = 1;
            } else {
                $frequencyTable[$token]++;
            }
        }
        return $frequencyTable;
    }
    /**
     * Deserialize from json
     *
     * @param  object  $json string or array
     * @return Bayes
     */
    public function fromJson($json) {
        $result = $json;
        // deserialize from json
        if (is_string($json)) {
            $result = json_decode($json, true);
        }
        $this->reset();
        // deserialize from json
        foreach ($this->STATE_KEYS as $k) {
            if (isset($result[$k])) {
                $this->{$k} = $result[$k];
            }
        }
        return $this;
    }
    /**
     * Make sure the category exists in dictionary
     *
     * @param  string  $categoryName
     * @return Bayes
     */
    public function initializeCategory($categoryName) {
        if (!isset($this->categorias[$categoryName])) {
            $this->quantidadeCategorias[$categoryName] = 0;
            $this->quantidadePalavras[$categoryName] = 0;
            $this->frequenciaPalavras[$categoryName] = [];
            $this->categorias[$categoryName] = true;
        }
        return $this;
    }
    /**
     * Teach your classifier
     *
     * @param  string  $text
     * @param  string  $category
     * @return Bayes
     */
    public function learn($text, $category) {
        $that = $this;
        // initialize category data structures if we've never seen this category
        $that->initializeCategory($category);
        // update our count of how many documents mapped to this category
        $that->quantidadeCategorias[$category]++;
        // update the total number of documents we have learned from
        $that->quantidadeSentencas++;
        // normalize the text into a word array
        $tokens = ($that->tokenizer)($text);
        // get a frequency count for each token in the text
        $frequencyTable = $that->frequencyTable($tokens);
        // Update vocabulario and word frequency count for this category
        foreach ($frequencyTable as $token => $frequencyInText) {
            // add this word to our vocabulario if not already existing
            if (!isset($that->vocabulario[$token])) {
                $that->vocabulario[$token] = true;
                $that->tamanhoVocabulario++;
            }
            // update the frequency information for this word in this category
            if (!isset($that->frequenciaPalavras[$category][$token])) {
                $that->frequenciaPalavras[$category][$token] = $frequencyInText;
            } else {
                $that->frequenciaPalavras[$category][$token] += $frequencyInText;
            }
            // update the count of all words we have seen mapped to this category
            $that->quantidadePalavras[$category] += $frequencyInText;
        }
        return $that;
    }
    /**
     * Extract the probabilities for each known category
     *
     * @param  string $text
     * @return array  probabilities by category or null
     */
    public function probabilities($text) {
        $that = $this;
        $probabilities = [];
        if ($that->quantidadeSentencas > 0) {
            $tokens = ($that->tokenizer)($text);
            $frequencyTable = $that->frequencyTable($tokens);
            // for this text
            // iterate thru our categorias to find the one with max probability
            foreach ($that->categorias as $category => $value) {
                $categoryProbability = $that->quantidadeCategorias[$category] / $that->quantidadeSentencas;
                $logProbability = log($categoryProbability);
                foreach ($frequencyTable as $token => $frequencyInText) {
                    $tokenProbability = $that->tokenProbability($token, $category);
                    // determine the log of the P( w | c ) for this word
                    $logProbability += $frequencyInText * log($tokenProbability);
                }
                $probabilities[$category] = $logProbability;
            }
        }
        return $probabilities;
    }
    /**
     * Reset the bayes class
     *
     * @return Bayes
     */
    public function reset() {
        if (!$this->options) {
            $this->options = [];
        }
        // set default tokenizer
        $this->tokenizer = function ($text) {
            // convert everything to lowercase
            $text = mb_strtolower($text);
            // split the words
            preg_match_all('/[[:alpha:]-]+/u', $text, $matches);
            //$matches = explode(",", str_replace(" ", "", $text));
            // first match list of words
            return $matches[0];
        };

        if (isset($this->options['tokenizer'])) {
            $this->tokenizer = $this->options['tokenizer'];
        }

        // add this word to our vocabulario if not already existing
        $this->categorias = [];
        // update the frequency information for this word in this category
        // update the count of all words we have seen mapped to this category
        $this->quantidadeCategorias = [];
        // iterate thru our categorias to find the one with max probability
        $this->quantidadeSentencas = 0;
        // for this text
        $this->vocabulario = [];
        $this->tamanhoVocabulario = 0;
        // iterate thru our categorias to find the one with max probability
        $this->quantidadePalavras = [];
        // for this text
        // determine the log of the P( w | c ) for this word
        $this->frequenciaPalavras = [];
        return $this;
    }
    /**
     * Serialize to json
     *
     * @return string the json string
     */
    public function toJson() {
        $result = [];
        // serialize to json
        foreach ($this->STATE_KEYS as $k) {
            $result[$k] = $this->{$k};
        }
        return json_encode($result);
    }
    /**
     * Calculate the probability that a `token` belongs to a `category`
     *
     * @param  string $token
     * @param  string $category
     * @return number the probability
     */
    public function tokenProbability($token, $category) {
        // how many times this word has occurred in documents mapped to this category
        $frequenciaPalavras = 0;
        if (isset($this->frequenciaPalavras[$category][$token])) {
            $frequenciaPalavras = $this->frequenciaPalavras[$category][$token];
        }
        // what is the count of all words that have ever been mapped to this category
        $quantidadePalavras = $this->quantidadePalavras[$category];
        // use laplace Add-1 Smoothing equation
        return ($frequenciaPalavras + 1) / ($quantidadePalavras + $this->tamanhoVocabulario);
    }
}
