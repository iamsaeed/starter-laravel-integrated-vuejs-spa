<?php

namespace Tests\Unit\Neuron\Workflows\Nodes;

use App\Neuron\Workflows\Events\UserRequestEvent;
use App\Neuron\Workflows\Nodes\StartNode;
use NeuronAI\Workflow\StartEvent;
use NeuronAI\Workflow\WorkflowState;
use PHPUnit\Framework\TestCase;

class StartNodeTest extends TestCase
{
    public function test_start_node_converts_start_event_to_user_request_event(): void
    {
        $node = new StartNode;
        $state = new WorkflowState;
        $state->set('user_message', 'Hello world');

        $event = $node(new StartEvent, $state);

        $this->assertInstanceOf(UserRequestEvent::class, $event);
    }

    public function test_start_node_extracts_user_message_from_state(): void
    {
        $node = new StartNode;
        $state = new WorkflowState;
        $state->set('user_message', 'Test message');

        $event = $node(new StartEvent, $state);

        $this->assertEquals('Test message', $event->message);
    }

    public function test_start_node_extracts_context_from_state(): void
    {
        $node = new StartNode;
        $state = new WorkflowState;
        $state->set('user_message', 'Test');
        $state->set('context', ['key' => 'value']);

        $event = $node(new StartEvent, $state);

        $this->assertEquals(['key' => 'value'], $event->context);
    }

    public function test_start_node_uses_empty_message_if_not_in_state(): void
    {
        $node = new StartNode;
        $state = new WorkflowState;

        $event = $node(new StartEvent, $state);

        $this->assertEquals('', $event->message);
    }

    public function test_start_node_uses_empty_context_if_not_in_state(): void
    {
        $node = new StartNode;
        $state = new WorkflowState;
        $state->set('user_message', 'Test');

        $event = $node(new StartEvent, $state);

        $this->assertEquals([], $event->context);
    }
}
