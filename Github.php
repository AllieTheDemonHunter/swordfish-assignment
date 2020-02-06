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
        $output = '<div class="collection">';
        foreach ($this as $key => $value) {
            $output .= $value;
        }
        $output .= '</div>';
        return $output;
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

class Base
{
    public $issues;

    public function __construct($incomingData)
    {
        foreach ($incomingData as $key => $issue) {
            $this->issues[] = new Issue($issue);
        }
    }

    public function __toString()
    {
        $issues = '<ul class="base">';
        foreach ($this->issues as $issue) {
            $issues .= '<li>';
            $issues .= $issue;
            $issues .= '</li>';
        }
        $issues .= '</ul>';

        return $issues;
    }
}

class Issue
{
    public $title;
    public $user;
    public $number;
    public $id;
    public $labels;
    public $state;
    public $assignee;
    public $assignees;
    public $created_at;
    public $updated_at;
    public $closed_at;
    public $body;

    public function __toString()
    {
        $issue_properties = $this;
        $issue_property = '<ul class="issue">';
        foreach ($issue_properties as $property) {
            $issue_property .= '<li>';
            if (is_array($property)) {
                $property = new Collection($property);
            }
            $issue_property .= $property . '</li>';
        }
        $issue_property .= '</ul>';

        return $issue_property;
    }

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

class User
{
    public $login;
    public $id;
    public $url;

    public function __toString()
    {

        $issue_property = '<ul class="user">';

        foreach ($this as $property) {
            $issue_property .= '<li>' . $property . '</li>';
        }
        $issue_property .= '</ul>';

        return $issue_property;
    }

    public function __construct($userData)
    {
        if (!empty($userData)) {
            $this->login = $userData->login;
            $this->id = $userData->id;
            $this->url = $userData->url;
        }
    }
}

class Label
{
    public $prefix;
    public $id;
    public $name;
    public $color;
    public $default;
    public $description;

    public function __toString()
    {
        $issue_property = '<ul class="label">';

        foreach ($this as $property) {
            $issue_property .= '<li>' . $property . '</li>';
        }

        $issue_property .= '</ul>';

        return $issue_property;
    }

    public function __construct($labelData)
    {
        $this->prefix = substr($labelData->name, 0, 1);
        $this->name = substr($labelData->name, 4, 1);
        $this->id = $labelData->id;
        $this->color = $labelData->color;
        $this->description = $labelData->description;
    }
}
