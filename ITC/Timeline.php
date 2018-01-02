<?php
namespace ITC;

use ITC\Timeline\Entry;

class Timeline
{


    /**
     * @var Entry[]
     */
    private $entries = [];


    public function addEntry(Entry $entry, $sort = false)
    {
        $this->entries[] = $entry;
        if($sort) {
            $this->sort();
        }

        return $this;
    }


    public function sort()
    {
        usort($this->entries, function($a, $b) {
            return $a->compare($b);
        });

        return $this;
    }


    public function getEntries()
    {
        return $this->entries;
    }


    public function render()
    {
        $str = '<ul class="timeline collapse-lg timeline-hairline">';
        foreach($this->entries as $entry) {
            $str .= $entry->render();
        }
        $str .= '</ul>';

        return $str;
    }


}