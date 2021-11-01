<?php

use Zheltikov\StdLib\Astar;

use function Zheltikov\StdLib\euclidean_distance;

require_once(__DIR__ . '/vendor/autoload.php');

class Edge
{

    protected $from;
    protected $to;

    public function __construct(Node &$from, Node &$to)
    {
        $this->to =& $to;
        $this->from =& $from;
    }

    /**
     * @return \Node
     */
    public function &getFrom(): Node
    {
        return $this->from;
    }

    /**
     * @return \Node
     */
    public function &getTo(): Node
    {
        return $this->to;
    }
}

class Node implements JsonSerializable
{
    protected $x;
    protected $y;

    /**
     * @var Node[]
     */
    protected $neighbors = [];

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function distance_to(self $that): float
    {
        return euclidean_distance($this->x, $this->y, $that->x, $that->y);
    }

    /**
     * @param \Edge[] $edges
     */
    public function findNeighbors(array $edges)
    {
        $this->neighbors = [];
        foreach ($edges as $edge) {
            if ($edge->getFrom() === $this) {
                $this->neighbors[] = $edge->getTo();
            }
            if ($edge->getTo() === $this) {
                $this->neighbors[] = $edge->getFrom();
            }
        }
    }

    /**
     * @return Node[]
     */
    public function getNeighors(): array
    {
        return $this->neighbors;
    }

    public function jsonSerialize()
    {
        $x = $this->x;
        $y = $this->y;
        // $neighbors = $this->neighbors;
        return compact(
            'x',
            'y',
        // 'neighbors',
        );
    }
}

$graph = [
    new Node(1, 1), // 0
    new Node(1, 3), // 1
    new Node(3, 2), // 2
    new Node(3, 3), // 3
    new Node(5, 2), // 4
    new Node(4, 4), // 5
    // new Node(3, 5),
    // new Node(5, 5),
];

$edges = [
    // 0
    new Edge($graph[0], $graph[1]),
    new Edge($graph[0], $graph[2]),

    // 1
    #new Edge($graph[1], $graph[0]),
    new Edge($graph[1], $graph[2]),
    new Edge($graph[1], $graph[3]),

    // 2
    #new Edge($graph[2], $graph[0]),
    #new Edge($graph[2], $graph[1]),
    new Edge($graph[2], $graph[3]),
    new Edge($graph[2], $graph[4]),
    new Edge($graph[2], $graph[5]),

    // 3
    #new Edge($graph[3], $graph[1]),
    #new Edge($graph[3], $graph[2]),
    new Edge($graph[3], $graph[5]),

    // 4
    #new Edge($graph[4], $graph[2]),
    new Edge($graph[4], $graph[5]),

    // 5
    #new Edge($graph[5], $graph[2]),
    #new Edge($graph[5], $graph[3]),
    #new Edge($graph[5], $graph[4]),
];

foreach ($graph as $node) {
    $node->findNeighbors($edges);
}

$start = reset($graph);
$goal = end($graph);

$h = function (Node $from) use ($goal): float {
    return $from->distance_to($goal);
};

$d = function (Node $from, Node $to): float {
    return $from->distance_to($to);
};

$get_neighbors = function (Node $node): array {
    return $node->getNeighors();
};

$a = new Astar($start, $goal, $h, $d, $get_neighbors);
$a->setup();

$result = $a->run();

// var_export($result);
echo json_encode(
                             $result,
    /*JSON_PRETTY_PRINT | */ JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
), "\n";
