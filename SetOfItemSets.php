<?php
/**
 * Created by PhpStorm.
 * User: MaxQ
 * Date: 04/02/2016
 * Time: 21:44
 */



class SetOfItemSets
{

    /**
     * @var array The set of item sets
     */
    public $itemSets = array();

    /**
     * @var int The item sets cardinality
     */
    public $cardinality;

    function setCardinality($cardinality)
    {
        $this->cardinality = $cardinality;
        return $this;
    }

    /**
     * @param array $itemSet
     */
    function findItemSets (array $itemSet)
    {
        $cardinality = $this->cardinality - 1;
        return array_filter($this->itemSets, function($entry) use ($itemSet, $cardinality) {
            return count(array_diff($entry, $itemSet)) == 1;
        });
    }

    /**
     * @param array $itemSet
     *
     * @return bool
     */
    function contains (array $itemSet)
    {
        return count(array_filter($this->itemSets, function($entry) use ($itemSet) {
            return count(array_diff($entry, $itemSet)) == 0;
        })) >= 1;
    }

    /**
     * @param array $itemSet
     *
     * @return $this
     */
    function addItemSet (array $itemSet)
    {
        // sanitize
        $itemSet = array_combine(array_keys($itemSet), array_values($itemSet));

        if(!$this->contains($itemSet))
        {
            $this->itemSets[] = $itemSet;
        }

        return $this;
    }
}


/**
 * @param array $itemSet
 *
 * @return array
 */
function aprioriGen(SetOfItemSets $sets)
{

    $superSet = new SetOfItemSets();

    $superSet->cardinality = $sets->cardinality + 1;

    foreach ($sets->itemSets as $idx => $set) {
        $candidates = $sets->findItemSets($set);
        if(count($candidates)>0){
            foreach ($candidates as $candidate) {
                $candidate = array_merge($set, $candidate);
                $superSet->addItemSet($candidate);
            }
        }
    }

    $toRemove = array();

    foreach ($superSet->itemSets as $idx => $set) {
        foreach ($set as $item) {
            if($sets->contains(array_diff($set, array($item))))
            {
                $toRemove[] = $idx;
            }
        }
    }

    foreach ($toRemove as $idx) {
        unset($superSet->itemSets[$idx]);
    }

    return $superSet;
}

$test = new SetOfItemSets();

$test->setCardinality(10)
    ->addItemSet(array('L1'))
    ->addItemSet(array('L2'))
    ->addItemSet(array('L3'))
    ->addItemSet(array('L4'))
    ->addItemSet(array('L5'))
    ->addItemSet(array('L6'));

var_dump(aprioriGen($test));