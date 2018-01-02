<?php
namespace ITC\Timeline;

use ITC\Model;

class Entry
{


    const ICON_HEART = 'fa-heartbeat';
    const ICON_HISTORY = 'fa-history';

    const STYLE_DEFAULT = 'style-default-bright';
    const STYLE_RED = 'style-danger';
    const STYLE_GREEN = 'style-success';

    const ICON_STYLE_DEFAULT = 'style-default';
    const ICON_STYLE_ACCENT = 'style-accent';
    const ICON_STYLE_PRIMARY = 'style-primary';
    const ICON_STYLE_GRAY = 'style-gray';
    const ICON_STYLE_RED = 'style-danger';
    const ICON_STYLE_GREEN = 'style-success';
    const ICON_STYLE_YELLOW = 'style-warning';


    /**
     * @var \DateTime
     */
    public $date;

    public $icon;

    public $title;

    public $body;

    public $style;

    public $iconStyle;


    public function __construct($date, $icon, $title, $body, $isMysqlDate = true, $style = self::STYLE_DEFAULT, $iconStyle = self::ICON_STYLE_PRIMARY)
    {
        if($isMysqlDate) $this->setMysqlDate($date);
        else $this->date = $date;

        $this->icon = $icon;
        $this->title = $title;
        $this->body = $body;
        $this->style = $style;
        $this->iconStyle = $iconStyle;
    }


    public function compare(Entry $o)
    {
        if($this->date < $o->date) {
            return 1;
        } elseif($this->date > $o->date) {
            return -1;
        } else {
            return 0;
        }
    }


    public function setMysqlDate($date)
    {
        $this->date = \DateTime::createFromFormat('Y-m-d', $date);
    }


    public function render()
    {
        $body = $this->body;
        if(is_array($body)) {
            $body = implode('<br>', $body);
        }
        $datum = Model::convertToHumanDate($this->date->format('Y-m-d'));

        $str = <<<EOT
        <li class="timeline-inverted">
        <div class="timeline-circ circ-xl $this->iconStyle"><i class="fa $this->icon"></i></div>
        <div class="timeline-entry">
            <div class="card $this->style">
                <div class="card-body small-padding">
                    <span class="text-lg text-bold">$this->title</span><br/>
                    <span class="opacity-50">$datum</span>
                    </div><!--end .card-body -->
                    <div class="card-body small-padding">
                        $body
                    </div><!--end .card-body -->
                    </div><!--end .card -->
                    </div><!--end .timeline-entry -->
                    </li>
EOT;

        return $str;
    }


}