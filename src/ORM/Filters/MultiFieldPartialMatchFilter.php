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
     * @throws InvalidArgumentException
     */
    public function setModifiers(array $modifiers): void
    {
        $modifiers = array_map('strtolower', $modifiers);

        if (($extras = array_diff($modifiers, ['not', 'nocase', 'case', 'splitwords'])) != []) {
            throw new InvalidArgumentException(
                get_class($this) . ' does not accept ' . implode(', ', $extras) . ' as modifiers'
            );
        }

        $this->modifiers = $modifiers;
        $this->subfilterModifiers = array_filter(
            $modifiers,
            function ($v): bool {
                return $v != 'splitwords';
            }
        );

        foreach ($this->subfilters as $subfilter) {
            $subfilter->setModifiers($this->subfilterModifiers);
        }
    }

    public function setSubfilters(array $fieldNames): void
    {
        $this->subfilters = [];
        foreach ($fieldNames as $fieldName) {
            $this->subfilters[] = PartialMatchFilter::create($fieldName, $this->value, $this->subfilterModifiers);
        }
    }

    /**
     * @param string $value
     */
    public function setValue($value): void
    {
        if ($this->shouldSplitWords() && is_string($value) && preg_match('/\s+/', $value)) {
            $value = preg_split('/\s+/', trim($value));
        }

        parent::setValue($value);

        foreach ($this->subfilters as $subfilter) {
            $subfilter->setValue($value);
        }
    }

    protected function shouldSplitWords(): bool
    {
        $modifiers = $this->getModifiers();
        return in_array('splitwords', $modifiers);
    }

    public function apply(DataQuery $dataQuery): DataQuery
    {
        $orGroup = $dataQuery->disjunctiveGroup();
        $orGroup = parent::apply($orGroup);

        foreach ($this->subfilters as $subfilter) {
            $orGroup = $subfilter->apply($orGroup);
        }

        // The original query will have been affected by the things added to $orGroup above
        // but returning this instead of that will cause new filters to be added as AND
        return $dataQuery;
    }
}
