<?php
/**
 * @PATCH IOC014: new formula engine (reaplce EvalMath)
 */

class FormulaException extends Exception {
    var $error;
    var $offset;

    function __construct($error, $offset) {
        parent::__construct($error . ' error at ' . ($offset + 1));
        $this->error = $error;
        $this->offset = $offset;
    }
}

class FormulaParser {

    static $symbols = array(
        'FormulaSymbolStart',
        'FormulaSymbolNumber',
        'FormulaSymbolPlus',
        'FormulaSymbolMinus',
        'FormulaSymbolAsterisk',
        'FormulaSymbolSlash',
        'FormulaSymbolPercent',
        'FormulaSymbolCaret',
        'FormulaSymbolAnd',
        'FormulaSymbolOr',
        'FormulaSymbolNot',
        'FormulaSymbolComparison',
        'FormulaSymbolIf',
        'FormulaSymbolElse',
        'FormulaSymbolId',
        'FormulaSymbolParentOpen',
        'FormulaSymbolParentClose',
        'FormulaSymbolComma',
        'FormulaSymbolEnd',
        'FormulaSymbolError',
    );

    var $tokens = array();
    var $token;

    function __construct($text) {
        $this->tokenize($text);
        $this->token = reset($this->tokens);
        $this->advance('Start');
        $this->expression = $this->expression(0);
        $this->advance('End');
        $this->expression->check('number');
    }

    function advance($symbol=null) {
        if ($symbol and !$this->lookahead($symbol)) {
            $this->token->error('syntax');
        }
        $result = $this->token;
        $this->token = next($this->tokens);
        return $result;
    }

    function expression($rbp) {
        $left = $this->advance()->nud();
        while ($rbp < $this->token->lbp) {
            $left = $this->advance()->led($left);
        }
        return $left;
    }

    function evaluate($vars) {
        return $this->expression->eval_($vars);
    }

    function lookahead($symbol) {
        return is_a($this->token, 'FormulaSymbol' . $symbol);
    }

    function tokenize($text) {
        $patterns = array('\s+'); // space
        foreach (self::$symbols as $symbol) {
            $patterns[] = '(' . $symbol::$pattern . ')';
        }
        $pattern = '/' . implode('|', $patterns) . '/i';
        $n_symbols = count(self::$symbols);
        $offset = 0;

        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $count = count($match);
            if ($count > 1) {
                $symbol = self::$symbols[$count - 2];
                $this->tokens[] = new $symbol($this, $offset, $match[$count - 1]);
            }
            $offset += strlen($match[0]);
        }
    }
}

class FormulaSymbol {
    var $lbp = 0;
    var $type = 'undefined';
    var $parser;
    var $offset;
    var $value;

    function __construct($parser, $offset, $value='') {
        $this->parser = $parser;
        $this->offset = $offset;
        $this->value = $value;
    }

    function check($type) {
        if ($this->type != $type) {
            $this->error('type');
        }
    }

    function error($name) {
        throw new FormulaException($name, $this->offset);
    }

    function led($left) {
        $this->error('syntax');
    }

    function nud() {
        $this->error('syntax');
    }
}

class FormulaSymbolNumber extends FormulaSymbol {
    static $pattern = '\d+(?:\.\d+)?|\.\d+';
    var $type = 'number';

    function eval_($vars) {
        return (float) $this->value;
    }

    function nud() {
        return $this;
    }
}

class FormulaSymbolInfix extends FormulaSymbol {
    var $operand_type;
    var $left;
    var $right;

    function check($type) {
        parent::check($type);
        if ($this->left) {
            $this->left->check($this->operand_type);
        }
        $this->right->check($this->operand_type);
    }

    function led($left) {
        $this->left = $left;
        $this->right = $this->parser->expression($this->lbp);
        return $this;
    }
}

class FormulaSymbolPlus extends FormulaSymbolInfix {
    static $pattern = '\+';
    var $lbp = 50;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
        return $this->left->eval_($vars) + $this->right->eval_($vars);
    }

    function nud() {
        return $this->parser->expression(80);
    }
}

class FormulaSymbolMinus extends FormulaSymbolInfix {
    static $pattern = '-';
    var $lbp = 50;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
        $left = $this->left ? $this->left->eval_($vars) : 0.0;
        return $left - $this->right->eval_($vars);
    }

    function nud() {
        $this->right = $this->parser->expression(80);
        return $this;
    }
}

class FormulaSymbolAsterisk extends FormulaSymbolInfix {
    static $pattern = '\*';
    var $lbp = 60;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
         return $this->left->eval_($vars) * $this->right->eval_($vars);
    }
}

class FormulaSymbolSlash extends FormulaSymbolInfix {
    static $pattern = '\/';
    var $lbp = 60;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
        if ($divisor = $this->right->eval_($vars)) {
            return $this->left->eval_($vars) / $divisor;
        } else {
            $this->error('eval');
        }
    }
}

class FormulaSymbolPercent extends FormulaSymbolInfix {
    static $pattern = '%';
    var $lbp = 60;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
        return $this->left->eval_($vars) % $this->right->eval_($vars);
    }
}

class FormulaSymbolCaret extends FormulaSymbolInfix {
    static $pattern = '\^';
    var $lbp = 70;
    var $type = 'number';
    var $operand_type = 'number';

    function eval_($vars) {
        return pow($this->left->eval_($vars), $this->right->eval_($vars));
    }
}

class FormulaSymbolComparison extends FormulaSymbolInfix {
    static $pattern = '!=|<=|>=|=|<|>';
    var $lbp = 40;
    var $type = 'boolean';
    var $operand_type = 'number';

    function eval_($vars) {
        $left = $this->left->eval_($vars);
        $right = $this->right->eval_($vars);
        switch ($this->value) {
        case '=': return $left == $right;
        case '!=': return $left != $right;
        case '<': return $left < $right;
        case '<=': return $left <= $right;
        case '>': return $left > $right;
        case '>=': return $left >= $right;
        }
    }

    function led($left) {
        $this->left = $left;
        $this->right = $this->parser->expression($this->lbp);
        $result = $this;
        while ($this->parser->lookahead('Comparison')) {
            $left = $result;
            $right = $this->parser->advance('Comparison');
            $right->left = $left->right;
            $right->right = $this->parser->expression($this->lbp);
            $result = new FormulaAnd($this->parser);
            $result->left = $left;
            $result->right = $right;
        }
        return $result;
    }
}

class FormulaSymbolAnd extends FormulaSymbolInfix {
    static $pattern = '\band\b';
    var $lbp = 30;
    var $type = 'boolean';
    var $operand_type = 'boolean';

    function eval_($vars) {
        return $this->left->eval_($vars) and $this->right->eval_($vars);
    }
}

class FormulaSymbolOr extends FormulaSymbolInfix {
    static $pattern = '\bor\b';
    var $lbp = 20;
    var $type = 'boolean';
    var $operand_type = 'boolean';

    function eval_($vars) {
        return $this->left->eval_($vars) or $this->right->eval_($vars);
    }
}

class FormulaSymbolNot extends FormulaSymbol {
    static $pattern = '\bnot\b';

    var $type = 'boolean';
    var $right;

    function check($type) {
        parent::check($type);
        $this->right->check('boolean');
    }

    function eval_($vars) {
        return !$this->right->eval_($vars);
    }

    function nud() {
        $this->right = $this->parser->expression(30);
        return $this;
    }
}

class FormulaSymbolIf extends FormulaSymbol {
    static $pattern = '\bif\b';
    var $lbp = 10;
    var $type = 'number';
    var $condition;
    var $first;
    var $second;

    function check($type) {
        parent::check($type);
        $this->condition->check('boolean');
        $this->first->check('number');
        $this->second->check('number');
    }

    function eval_($vars) {
        return ($this->condition->eval_($vars) ?
                $this->first->eval_($vars) : $this->second->eval_($vars));
    }

    function led($left) {
        $this->first = $left;
        $this->condition = $this->parser->expression(0);
        $this->parser->advance('Else');
        $this->second = $this->parser->expression(0);
        return $this;
    }
}

class FormulaSymbolElse extends FormulaSymbol {
    static $pattern = '\belse\b';
}

class FormulaSymbolComma extends FormulaSymbol {
    static $pattern = ',';
}

class FormulaSymbolParentOpen extends FormulaSymbol {
    static $pattern = '\(';

    function nud() {
        $result = $this->parser->expression(0);
        $this->parser->advance('ParentClose');
        return $result;
    }
}

class FormulaSymbolParentClose extends FormulaSymbol {
    static $pattern = '\)';
}

class FormulaSymbolId extends FormulaSymbol {
    static $pattern = '[a-zA-Z_][a-zA-Z0-9_]*';
    var $type = 'number';

    function eval_($vars) {
        return isset($vars[$this->value]) ? $vars[$this->value] : 0.0;
    }

    function nud() {
        if ($this->parser->lookahead('ParentOpen')) {
            return new FormulaSymbolFunction($this->parser, $this->offset,  $this->value);
        }
        return $this;
    }
}

class FormulaSymbolFunction extends FormulaSymbol {
    var $type = 'number';
    var $hasgrade;

    static $functions = array(
        'abs' => array('builtin' => 'abs'),
        'acos' => array('builtin' => 'acos'),
        'acosh' => array('builtin' => 'acosh'),
        'arccos' => array('builtin' => 'acos'),
        'arccosh' => array('builtin' => 'acosh'),
        'arcsin' => array('builtin' => 'asin'),
        'arcsinh' => array('builtin' => 'asinh'),
        'arctan' => array('builtin' => 'atan'),
        'arctanh' => array('builtin' => 'atanh'),
        'asin' => array('builtin' => 'asin'),
        'asinh' => array('builtin' => 'asinh'),
        'atan' => array('builtin' => 'atan'),
        'atanh' => array('builtin' => 'atanh'),
        'average' => array('n_args' => array(1, null)),
        'ceil' => array('builtin' => 'ceil'),
        'cos' => array('builtin' => 'cos'),
        'cosh' => array('builtin' => 'cosh'),
        'count' => array('n_args' => array(0, null),'type_args' => 'boolean'),
        'exp' => array('builtin' => 'exp'),
        'floor' => array('builtin' => 'floor'),
        'has_grade' => array('n_args' => 1),
        'ln' => array('builtin' => 'log'),
        'log' => array('builtin' => 'log'),
        'max' => array('n_args' => array(2, null), 'builtin' => 'max'),
        'min' => array('n_args' => array(2, null), 'builtin' => 'min'),
        'mod' => array('n_args' => 2),
        'pi' => array('n_args' => 0, 'builtin' => 'pi'),
        'power' => array('n_args' => 2, 'builtin' => 'pow'),
        'rand_float' => array('n_args' => 0),
        'rand_int' => array('n_args' => 2, 'builtin' => 'mt_rand'),
        'round' => array('n_args' => array(1, 2), 'builtin' => 'round'),
        'sin' => array('builtin' => 'sin'),
        'sinh' => array('builtin' => 'sinh'),
        'sqrt' => array('builtin' => 'sqrt'),
        'sum' => array('n_args' => array(0, null)),
        'tan' => array('builtin' => 'tan'),
        'tanh' => array('builtin' => 'tanh'),
    );

    var $arguments = array();

    function __construct($parser, $offset, $value) {
        parent::__construct($parser, $offset, $value);
        $this->parser->advance('ParentOpen');
        if (!$this->parser->lookahead('ParentClose')) {
            $this->arguments[] = $this->parser->expression(0);
            while ($this->parser->lookahead('Comma')) {
                $this->parser->advance('Comma');
                $this->arguments[] = $this->parser->expression(0);
            }
        }
        $this->parser->advance('ParentClose');
    }

    function check($type) {
        parent::check($type);

        $name = strtolower($this->value);

        if (!isset(self::$functions[$name])) {
            $this->error('syntax');
        }

        list($min_args, $max_args) = $this->n_args($name);
        if (count($this->arguments) < $min_args or
            count($this->arguments) > $max_args and $max_args !== null) {
            $this->error('syntax');
        }

        $type_args = $this->type_args($name);
        foreach ($this->arguments as $argument) {
            $argument->check($type_args);
        }
    }

    function eval_($vars) {
        $args = array();
        $this->hasgrade = true;
        foreach ($this->arguments as $argument) {
            $args[] = $argument->eval_($vars);
            if ($args[0] == -(PHP_INT_MAX - 1)) {
                $this->hasgrade = false;
            }
        }
        $name = strtolower($this->value);
        if (!empty(self::$functions[$name]['builtin'])) {
            $func = self::$functions[$name]['builtin'];
            return call_user_func_array($func, $args);
        } else {
            return call_user_func(array($this, 'eval_'.$name) , $args);
        }
    }

    function eval_average($args) {
        return array_sum($args) / count($args);
    }

    function eval_count($args) {
        return array_sum($args);
    }

    function eval_mod($args) {
        return $args[0] % $args[1];
    }

    function eval_rand_float($args) {
        return mt_rand() / mt_getrandmax();
    }

    function eval_sum($args) {
        return array_sum($args);
    }

    function eval_has_grade($args) {
        return intval($this->hasgrade);
    }

    function n_args($name) {
        if (isset(self::$functions[$name]['n_args'])) {
            $n_args = self::$functions[$name]['n_args'];
            return is_array($n_args) ? $n_args : array($n_args, $n_args);
        }
        return array(1, 1);
    }

    function type_args($name) {
        if (isset(self::$functions[$name]['type_args'])) {
            return self::$functions[$name]['type_args'];
        }
        return 'number';
    }
}

class FormulaSymbolStart extends FormulaSymbol {
    static $pattern = '^=';
    var $type = 'number';

    function nud() {
        return $this->parser->expression(0);
    }
}

class FormulaSymbolEnd extends FormulaSymbol {
    static $pattern = '$';
}

class FormulaSymbolError extends FormulaSymbol {
    static $pattern = '.';
}
