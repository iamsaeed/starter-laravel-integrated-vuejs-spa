<?php

namespace App\Neuron\Workflows;

use App\Neuron\Nodes\ChatInputNode;
use App\Neuron\Nodes\MultiAgentRouterNode;
use App\Neuron\Nodes\ResponseFormatterNode;
use NeuronAI\Workflow\Edge;
use NeuronAI\Workflow\Workflow;

/**
 * Simple code-based workflow for MVP
 * No database configuration needed - all logic in code
 */
class ChatWorkflow extends Workflow
{
    /**
     * Define the nodes in the workflow
     */
    public function nodes(): array
    {
        return [
            new ChatInputNode,           // Receives user message
            new MultiAgentRouterNode,    // Smart routing logic
            new ResponseFormatterNode,    // JSON to natural language
        ];
    }

    /**
     * Define the edges (connections) between nodes
     */
    public function edges(): array
    {
        return [
            new Edge(ChatInputNode::class, MultiAgentRouterNode::class),
            new Edge(MultiAgentRouterNode::class, ResponseFormatterNode::class),
        ];
    }
}
