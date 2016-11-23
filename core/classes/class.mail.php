<?

class Mail {
    private $subject = '';
    private $recipient = '';
    private $message = '';
    private $header;

    public function __construct() {
        $this->header = 'MIME-Version: 1.0' . "\r\n";
        $this->header.= 'Content-type: text/plain; charset=utf-8' . "\r\n";
    }

    public function subject($s) {
        $this->subject = $s;
    }

    public function recipient($r) {
        $this->recipient = $r;
    }

    public function addMessage($m) {
        $this->message.=$m;
    }

    public function send() {
        mail(
            $this->recipient,
            $this->subject,
            $this->message,
            $this->header
        );
    }

}