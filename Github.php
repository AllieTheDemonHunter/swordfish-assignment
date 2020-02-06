<?php

/**
 * This is the base Github interface.
 */

namespace GitAllie;

use DateTimeZone;

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
            $output .= '<li>' . $value . '</li>';
        }

        $output .= '</ul>';
        return $output;
    }
}

class Github
{
    public function __toString()
    {
        $caller = explode('\\', get_called_class());
        $invoked = array_pop($caller);
        $out = '<ul class="' . strtolower($invoked) . '">';
        foreach ($this as $name => $property) {
            // The order here is important/
            $out .= '<li class="'.strtolower($name).'">';
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
}

class When extends \DateTime
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

class Base extends Github
{
    public $issues;

    public function __construct($incomingData)
    {
        foreach ($incomingData as $key => $issue) {
            $this->issues[] = new Issue($issue);
        }
    }
}

class Issue extends Github
{
    public $number;
    public $title;
    public $body;

    public $labels;
    public $assignee;
    public $state;

    public $assignees;
    public $user;
    public $id;
    public $created_at;
    public $updated_at;
    public $closed_at;

    public function __construct($issueData)
    {
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
        $this->body = $issueData->body;

        // These might throw exceptions.
        try {
            $this->created_at = new When($issueData->created_at);
            $this->closed_at = new When($issueData->closed_at);
            $this->updated_at = new When($issueData->updated_at);
        } catch (\Exception $e) {
        }

    }
}

class User extends Github
{
    public $login;
    public $id;
    public $url;

    public function __construct($userData)
    {
        if (!empty($userData)) {
            $this->login = $userData->login;
            $this->id = $userData->id;
            $this->url = $userData->url;
        }
    }
}

class Label extends Github
{
    public $description;
    public $name;

    public function __construct($labelData)
    {
        $this->name = substr($labelData->name, 3);
        $this->description = $labelData->description;
    }
}
