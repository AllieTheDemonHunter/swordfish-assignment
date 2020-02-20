<?php

namespace GitAllie;

use \DateTime;

class Collection extends \ArrayObject
{
    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }

    public function __toString()
    {
        $caller = explode('\\', get_called_class());
        $invoked = array_pop($caller);
        $output = '<ul class="' . strtolower($invoked) . '">';

        foreach ($this as $key => $value) {
            $item_class = '';
            if (isset($value->description)) {
                $item_class = strtolower(str_replace(' ', '-', $value->description));
            }
            $output .= '<li class="' . $item_class . '">' . $value . '</li>';
        }

        $output .= '</ul>';
        return $output;
    }
}

class CollectionTable extends \ArrayObject
{
    public $header_row;

    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }

    public function make_header()
    {
        $this->header_row = '<th>';
        $definition = new Issue();
        foreach ($definition as $key => $value) {
            $this->header_row .= '<td class="' . strtolower($key) . '">' . $key . '</td>';
        }
        $this->header_row .= '</th>';
    }

    public function __toString()
    {
        $caller = explode('\\', get_called_class());
        $invokedBy = implode(' ', $caller);
        $output = $this->header_row;
        $output .= '<tr class="' . strtolower($invokedBy) . '">';

        //This should be various columns for each line.
        // $this is currently a GitAllie\CollectionTable
        foreach ($this as $key => $value) {
            // $value here is something like number, Label, User (assignee) or Title..

            if (is_int($value)) {
                $output .= '<td class="' . $key . '">' . strip_tags($value) . '</td>';
            }

            if (is_string($value)) {
                $output .= '<td class="' . $key . '">' . strip_tags($value) . '</td>';
            }

            if (is_array($value)) {
                $content = '<ul class="' . $key . '-children">';
                foreach ($value as $content_key => $content_node) {
                    $content .= '<li>';
                    if ($content_node instanceof User) {
                        $content .= $content_node->login;
                    } else {
                        $content .= $content_node->name;
                    }

                    $content .= '</li>';
                }
                $content .= '</ul>';
                $output .= '<td class="' . $key . '">' . $content . '</td>';
            }

            if ($value instanceof Issue) {
                $output .= '<td class="issue">' . print_r($value, 1) . '</td>';
            }

            if ($value instanceof User) {
                $output .= '<td class="user">' . $value->login . '</td>';
            }
        }

        $output .= '</tr>';
        return $output;
    }
}

class gitHubView
{
    public function __toString()
    {
//        return $this->to_list();
        return $this->to_table();
    }

    public function to_list()
    {
        $caller = explode('\\', get_called_class());
        $invoked = array_pop($caller);
        $out = '<ul class="' . strtolower($invoked) . '">';
        foreach ($this as $name => $property) {
            // The order here is important/
            $out .= '<li class="' . strtolower($name) . '">';
            $name = ucwords(implode(' ', explode('_', $name)));

            if (is_array($property)) {
                $out .= '<h4 class="friendly-name">' . $name . '</h4>';
                $out .= new Collection($property);
            } else {
                $out .= '<h4 class="friendly-name">' . $name . '</h4>' . $property . '</li>';
            }
        }
        $out .= '</ul>';

        return $out;
    }

    public function to_table()
    {
        $caller = explode('\\', get_called_class());
        $invoked = array_pop($caller);
        $out = '<table class="' . strtolower($invoked) . '">';
        if (isset($this->issues)) {
            foreach ($this->issues as $key => $issue) {
                // The order here is important
                $out .= new CollectionTable($issue);
            }
        }
        $out .= '</table>';

        return $out;
    }
}

class When extends DateTime
{
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);
    }

    public function __toString()
    {
        return $this->format('Y-d-m');
    }
}

class Base extends gitHubView
{
    use gitHubTrait;

    public $issues;
    /**
     * @var array
     */
    public $labels;

    public function __construct(gitHubCommander $incomingData)
    {
        if (!empty($incomingData)) {
            foreach ($incomingData->issues as $key => $issue) {
                $this->issues[] = new Issue($issue);
            }

            foreach ($incomingData->labels as $key => $label) {
                $this->labels[] = new Label($label);
            }
        } else {
            print 'No incomingData';
        }
    }
}

class Issue extends gitHubView
{
    public $number;
    public $title;
    public $state;

    public $body;
    public $assignee;
    public $labels;

    public $assignees;
    public $user;
    public $id;
    public $created_at;
    public $updated_at;
    public $closed_at;

    public function __construct($issueData = false)
    {
        if (!$issueData) {
            //Get return this definition.
            return get_class_vars(self::class);
        }
        $this->user = new User($issueData->user);

        if (!empty($issueData->labels)) {
            foreach ($issueData->labels as $label) {
                $this->labels[] = new Label($label);
            }
        }

        if (!empty($issueData->assignees)) {
            foreach ($issueData->assignees as $user) {
                $this->assignees[] = new User($user);
            }
        }

        $this->title = $issueData->title;
        $this->number = $issueData->number;
        $this->id = $issueData->id;
        $this->state = $issueData->state;
        $this->assignee = new User($issueData->assignee);
        $this->body = strip_tags($issueData->body);

        // These might throw exceptions.
        try {
            $this->created_at = new When($issueData->created_at);
            $this->closed_at = new When($issueData->closed_at);
            $this->updated_at = new When($issueData->updated_at);
        } catch (\Exception $e) {
        }
    }
}

class User extends gitHubView
{
    public $login;
    public $id;
    public $url;

    public function __construct($userData)
    {
        if (empty($userData)) {
            $this->login = '-';
        } else {
            $this->login = $userData->login;
            $this->id = $userData->id;
            $this->url = $userData->url;
        }
    }
}

class Label extends gitHubView
{
    public $description;
    public $name;
    public $type;
    public $id;
    public $name_raw;

    public function __construct($labelData)
    {
        if (empty($labelData)) {
            $this->name = '-';
            $this->description = '-';
        } else {
            $this->name_raw = $labelData->name;
            $this->name = substr($labelData->name, 3);
            $this->type = substr($labelData->name, 0, 1);
            $this->description = $labelData->description;
            $this->id = $labelData->id;
        }
    }
}