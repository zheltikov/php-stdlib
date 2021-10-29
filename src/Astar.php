<?php

namespace Zheltikov\StdLib;

/**
 *
 */
class Astar
{
    protected $open_set;
    protected $came_from;
    protected $g_score;
    protected $f_score;

    protected $start;
    protected $goal;
    protected $h;

    protected $d;
    protected $get_neighbors;
    protected $serializer;

    // A* finds a path from start to goal.
    // h is the heuristic function. h(n) estimates the cost to reach goal from node n.
    public function __construct($start, $goal, $h, $d, $get_neighbors, $serializer = 'serialize')
    {
        $this->start = $start;
        $this->goal = $goal;
        $this->h = $h;

        $this->d = $d;
        $this->get_neighbors = $get_neighbors;
        $this->serializer = $serializer;
    }

    public function setup()
    {
        // The set of discovered nodes that may need to be (re-)expanded.
        // Initially, only the start node is known.
        // This is usually implemented as a min-heap or priority queue rather than a hash-set.
        $this->open_set = [$this->start];

        // For node n, cameFrom[n] is the node immediately preceding it on the cheapest path from start
        // to n currently known.
        $this->came_from = [];

        // For node n, gScore[n] is the cost of the cheapest path from start to n currently known.
        $this->g_score = [];
        $this->setGScore($this->start, 0);

        // For node n, fScore[n] := gScore[n] + h(n). fScore[n] represents our current best guess as to
        // how short a path from start to finish can be if it goes through n.
        $this->f_score = [];
        $this->setFScore(
            $this->start,
            ($this->h)($this->start)
        );
    }

    protected function getFScore($node)
    {
        $s = ($this->serializer)($node);
        if (!array_key_exists($s, $this->f_score)) {
            $this->f_score[$s] = INF;
        }
        return $this->f_score[$s];
    }

    protected function setFScore($node, $f_score)
    {
        $this->f_score[($this->serializer)($node)] = $f_score;
    }

    protected function getGScore($node)
    {
        $s = ($this->serializer)($node);
        if (!array_key_exists($s, $this->g_score)) {
            $this->g_score[$s] = INF;
        }
        return $this->g_score[$s];
    }

    protected function setGScore($node, $g_score)
    {
        $this->g_score[($this->serializer)($node)] = $g_score;
    }

    protected function getCurrent()
    {
        // the node in openSet having the lowest fScore[] value
        $lowest = INF;
        $a = null;
        foreach ($this->open_set as $node) {
            $x = $this->getFScore($node);
            if ($x < $lowest) {
                $lowest = $x;
                $a = $node;
            }
        }
        return $a ?? reset($this->open_set);
    }

    public function run()
    {
        while (count($this->open_set) !== 0) {
            // This operation can occur in O(1) time if openSet is a min-heap or a priority queue
            $current = $this->getCurrent();

            if ($current === $this->goal) {
                return $this->reconstruct_path($this->came_from, $current);
            }

            $this->removeFromSet($this->open_set, $current);

            $neighbors = ($this->get_neighbors)($current);
            foreach ($neighbors as $neighbor) {
                // d(current,neighbor) is the weight of the edge from current to neighbor
                // tentative_gScore is the distance from start to the neighbor through current
                $tentative_g_score = $this->getGScore($current) + ($this->d)($current, $neighbor);

                if ($tentative_g_score < $this->getGScore($neighbor)) {
                    // This path to neighbor is better than any previous one. Record it!
                    $this->came_from[($this->serializer)($neighbor)] = $current;
                    $this->setGScore($neighbor, $tentative_g_score);
                    $this->setFScore(
                        $neighbor,
                        $this->g_score[($this->serializer)($neighbor)] + ($this->h)($neighbor)
                    );

                    if (!in_array($neighbor, $this->open_set, true)) {
                        $this->open_set[] = $neighbor;
                    }
                }
            }
        }

        // Open set is empty but goal was never reached
        return null;
    }

    public function reconstruct_path($came_from, $current)
    {
        $total_path = [$current];
        while (in_array(($this->serializer)($current), array_keys($came_from), true)) {
            $current = $came_from[($this->serializer)($current)];
            array_unshift($total_path, $current);
        }
        return $total_path;
    }

    // -------------------------------------------------------------------------

    protected function removeFromSet(array &$set, $element)
    {
        $result = [];
        foreach ($set as $value) {
            if ($value !== $element) {
                $result[] = $value;
            }
        }
        $set = $result;
    }
}
