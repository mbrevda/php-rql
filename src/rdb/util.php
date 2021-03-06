<?php namespace r;

// ------------- RethinkDB Backtraces -------------
class Backtrace
{
    static public function _fromJSON($backtrace) {
        $result = new Backtrace();
        $result->frames = array();
        foreach ($backtrace as $frame)
            $result->frames[] = Frame::_fromJSON($frame);
        return $result;
    }

    // Returns true if no more frames are available
    public function _consumeFrame() {
        if (\count($this->frames) == 0) return false;
        $frame = $this->frames[0];
        $this->frames = array_slice($this->frames, 1);
        return $frame;
    }
    
    private $frames = null;
}

class Frame
{
    static public function _fromJSON($frame) {
        $result = new Frame();
        if (is_string($frame)) {
            $result->isOptionalArg = true;
            $result->optionalArgName = $frame;
        } else {
            $result->isPositionalArg = true;
            $result->positionalArgPosition = $frame;
        }

        return $result;
    }
    public function isPositionalArg() { return $this->isPositionalArg; }
    public function isOptionalArg() { return $this->isOptionalArg; }
    public function getOptionalArgName() { return $this->optionalArgName; }
    public function getPositionalArgPosition() { return $this->positionalArgPosition; }
    
    private $isPositionalArg = false;
    private $isOptionalArg = false;
    private $optionalArgName = null;
    private $positionalArgPosition = null;
}

// ------------- RethinkDB Exceptions -------------
class RqlDriverError extends \Exception
{
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
    
    public function __toString() {
        return "RqlDriverError:\n  " . $this->getMessage() . "\n";
    }
}

class RqlServerError extends \Exception
{
    public function __construct($message, $query = null, $backtrace = null, $code = 0) {
        $this->query = $query;
        $this->backtrace = $backtrace;
        parent::__construct($message, $code);
    }
    
    public function __toString() {
        return "RqlServerError:\n  " . $this->getMessage() . "\n" . $this->getBacktraceString();
    }
    
    public function getBacktraceString() {
        $result = "";
        if (isset($this->query)) {
            $result .= "  Failed query:\n";
            $nullBacktrace = null;
            $result .= "    " . $this->query->_toString($nullBacktrace) . "\n";
            if (isset($this->backtrace)) {
                $backtraceCopy = $this->backtrace;
                $result .= "    " . $this->query->_toString($backtraceCopy) . "\n";
            }
        }
        return $result;
    }
    
    private $query;
    private $backtrace;
}

?>
