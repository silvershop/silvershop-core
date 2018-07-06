<?php

namespace SilverShop\ORM\Filters;

use InvalidArgumentException;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\Filters\PartialMatchFilter;

/**
 * This must be created manually OR the subfilters set manually
 * because of the way SS cleans up the filter definitions before
 * passing them to the filter classes.
 */
class MultiFieldPartialMatchFilter extends PartialMatchFilter
{
    /**
     * @var array $subfilters
     */
    protected $subfilters;

    /**
     * @var array $subfilterModifiers
     */
    protected $subfilterModifiers;

    /**
     * @param string $fullName    Determines the name of the field, as well as the searched database
     *                            column. Can contain a relation name in dot notation, which will
     *                            automatically join the necessary tables (e.g. "Comments.Name" to
     *                            join the "Comments" has-many relationship and search the "Name"
     *                            column when applying this filter to a SiteTree class).
     * @param mixed  $value       [optional]
     * @param array  $modifiers   [optional]
     * @param array  $otherFields [optional]
     */
    public function __construct($fullName, $value = false, array $modifiers = [], array $otherFields = [])
    {
        parent::__construct($fullName, $value, $modifiers);
        if ($value !== false) {
            $this->setValue($value);
        }
        $this->setSubfilters($otherFields);
    }

    /**
     * @param array $modifiers
     *
     * @throws InvalidArgumentException
     */
    public function setModifiers(array $modifiers)
    {
        $modifiers = array_map('strtolower', $modifiers);

        if (($extras = array_diff($modifiers, ['not', 'nocase', 'case', 'splitwords'])) != array()) {
            throw new InvalidArgumentException(
                get_class($this) . ' does not accept ' . implode(', ', $extras) . ' as modifiers'
            );
        }

        $this->modifiers = $modifiers;
        $this->subfilterModifiers = array_filter(
            $modifiers,
            function ($v) {
                return $v != 'splitwords';
            }
        );

        if (!empty($this->subfilters)) {
            foreach ($this->subfilters as $f) {
                $f->setModifiers($this->subfilterModifiers);
            }
        }
    }

    /**
     * @param array $fieldNames
     */
    public function setSubfilters(array $fieldNames)
    {
        $this->subfilters = array();
        if (count($fieldNames) > 0) {
            foreach ($fieldNames as $name) {
                $this->subfilters[] = new PartialMatchFilter($name, $this->value, $this->subfilterModifiers);
            }
        }
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        if ($this->shouldSplitWords() && is_string($value) && preg_match('/\s+/', $value)) {
            $value = preg_split('/\s+/', trim($value));
        }

        parent::setValue($value);

        if (count($this->subfilters) > 0) {
            foreach ($this->subfilters as $f) {
                $f->setValue($value);
            }
        }
    }

    /**
     * @return bool
     */
    protected function shouldSplitWords()
    {
        $modifiers = $this->getModifiers();
        return in_array('splitwords', $modifiers);
    }

    /**
     * @param DataQuery $query
     *
     * @return $this|DataQuery
     */
    public function apply(DataQuery $query)
    {
        $orGroup = $query->disjunctiveGroup();
        $orGroup = parent::apply($orGroup);

        if (count($this->subfilters) > 0) {
            foreach ($this->subfilters as $f) {
                $orGroup = $f->apply($orGroup);
            }
        }

        // The original query will have been affected by the things added to $orGroup above
        // but returning this instead of that will cause new filters to be added as AND
        return $query;
    }
}
