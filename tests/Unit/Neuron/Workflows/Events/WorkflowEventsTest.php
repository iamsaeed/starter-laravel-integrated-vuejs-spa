<?php

namespace Tests\Unit\Neuron\Workflows\Events;

use App\Neuron\Workflows\Events\AnalysisTaskEvent;
use App\Neuron\Workflows\Events\BlogWritingTaskEvent;
use App\Neuron\Workflows\Events\CodeTaskEvent;
use App\Neuron\Workflows\Events\EmailDesignTaskEvent;
use App\Neuron\Workflows\Events\EmailDraftingTaskEvent;
use App\Neuron\Workflows\Events\ExpenseTaskEvent;
use App\Neuron\Workflows\Events\MultiAgentTaskEvent;
use App\Neuron\Workflows\Events\ResearchTaskEvent;
use App\Neuron\Workflows\Events\SearchEngineTaskEvent;
use App\Neuron\Workflows\Events\UserRequestEvent;
use NeuronAI\Workflow\Event;
use PHPUnit\Framework\TestCase;

class WorkflowEventsTest extends TestCase
{
    public function test_user_request_event_implements_event_interface(): void
    {
        $event = new UserRequestEvent('test message');

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_user_request_event_stores_message(): void
    {
        $event = new UserRequestEvent('test message', ['key' => 'value']);

        $this->assertEquals('test message', $event->message);
        $this->assertEquals(['key' => 'value'], $event->context);
    }

    public function test_user_request_event_has_empty_context_by_default(): void
    {
        $event = new UserRequestEvent('test message');

        $this->assertEquals([], $event->context);
    }

    public function test_multi_agent_task_event_implements_event_interface(): void
    {
        $event = new MultiAgentTaskEvent(['agent1', 'agent2'], 'test task');

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_multi_agent_task_event_stores_agents_and_task(): void
    {
        $event = new MultiAgentTaskEvent(
            ['research', 'analysis'],
            'Research AI and analyze findings',
            ['param' => 'value']
        );

        $this->assertEquals(['research', 'analysis'], $event->agents);
        $this->assertEquals('Research AI and analyze findings', $event->task);
        $this->assertEquals(['param' => 'value'], $event->parameters);
    }

    public function test_multi_agent_task_event_has_empty_parameters_by_default(): void
    {
        $event = new MultiAgentTaskEvent(['agent1'], 'task');

        $this->assertEquals([], $event->parameters);
    }

    public function test_expense_task_event_implements_event_interface(): void
    {
        $event = new ExpenseTaskEvent('Add expense', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_expense_task_event_stores_task_and_parameters(): void
    {
        $event = new ExpenseTaskEvent('Add $50 expense', ['amount' => 50]);

        $this->assertEquals('Add $50 expense', $event->task);
        $this->assertEquals(['amount' => 50], $event->parameters);
    }

    public function test_research_task_event_implements_event_interface(): void
    {
        $event = new ResearchTaskEvent('Research Laravel', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_research_task_event_stores_task_and_parameters(): void
    {
        $event = new ResearchTaskEvent('Research AI trends', ['depth' => 'comprehensive']);

        $this->assertEquals('Research AI trends', $event->task);
        $this->assertEquals(['depth' => 'comprehensive'], $event->parameters);
    }

    public function test_analysis_task_event_implements_event_interface(): void
    {
        $event = new AnalysisTaskEvent('Analyze data', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_analysis_task_event_stores_task(): void
    {
        $event = new AnalysisTaskEvent('Analyze sales data');

        $this->assertEquals('Analyze sales data', $event->task);
    }

    public function test_email_drafting_task_event_implements_event_interface(): void
    {
        $event = new EmailDraftingTaskEvent('Draft email', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_email_drafting_task_event_stores_task(): void
    {
        $event = new EmailDraftingTaskEvent('Draft cancellation email');

        $this->assertEquals('Draft cancellation email', $event->task);
    }

    public function test_blog_writing_task_event_implements_event_interface(): void
    {
        $event = new BlogWritingTaskEvent('Write blog', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_blog_writing_task_event_stores_task(): void
    {
        $event = new BlogWritingTaskEvent('Write blog about Laravel');

        $this->assertEquals('Write blog about Laravel', $event->task);
    }

    public function test_search_engine_task_event_implements_event_interface(): void
    {
        $event = new SearchEngineTaskEvent('Search for Laravel', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_search_engine_task_event_stores_task(): void
    {
        $event = new SearchEngineTaskEvent('What is Laravel?');

        $this->assertEquals('What is Laravel?', $event->task);
    }

    public function test_email_design_task_event_implements_event_interface(): void
    {
        $event = new EmailDesignTaskEvent('Design email', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_email_design_task_event_stores_task(): void
    {
        $event = new EmailDesignTaskEvent('Design newsletter template');

        $this->assertEquals('Design newsletter template', $event->task);
    }

    public function test_code_task_event_implements_event_interface(): void
    {
        $event = new CodeTaskEvent('Write function', []);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_code_task_event_stores_task(): void
    {
        $event = new CodeTaskEvent('Write Python sort function');

        $this->assertEquals('Write Python sort function', $event->task);
    }

    public function test_all_task_events_are_readonly(): void
    {
        $event = new ExpenseTaskEvent('test', []);

        $this->expectException(\Error::class);
        $event->task = 'modified'; // Should throw error for readonly property
    }
}
