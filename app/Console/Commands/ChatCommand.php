<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Neuron\Events\ChatEvent;
use App\Neuron\Nodes\MultiAgentRouterNode;
use Illuminate\Console\Command;

class ChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'chat {message? : The message to send}
                            {--user= : User ID (default: 1)}
                            
                            {--interactive : Run in interactive mode}';

    /**
     * The console command description.
     */
    protected $description = 'Test the chat workflow with AI-powered multi-agent routing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🤖 AI Chat Workflow - MVP Demo');
        $this->newLine();

        if ($this->option('interactive')) {
            return $this->runInteractiveMode();
        }

        $message = $this->argument('message');

        if (! $message) {
            $message = $this->ask('💬 Enter your message');
        }

        if (! $message) {
            $this->error('Message is required');

            return self::FAILURE;
        }

        return $this->processMessage($message);
    }

    /**
     * Run the chat in interactive mode
     */
    protected function runInteractiveMode(): int
    {
        $this->info('🔄 Interactive mode - type "exit" or "quit" to stop');
        $this->newLine();

        while (true) {
            $message = $this->ask('💬 You');

            if (! $message || in_array(strtolower($message), ['exit', 'quit', 'q'])) {
                $this->info('👋 Goodbye!');
                break;
            }

            $this->processMessage($message);
            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * Process a single message through the workflow
     */
    protected function processMessage(string $message): int
    {
        try {
            // Get user
            $userId = $this->option('user') ?? 2;
            $user = User::find($userId);
            if (! $user) {
                $this->error("User with ID {$userId} not found");

                return self::FAILURE;
            }

            // For demo purposes, we'll work with the central database
            $this->comment("Processing message for User: {$user->name} (ID: {$userId})");
            $this->newLine();

            // Create event
            $event = new ChatEvent([
                'message' => $message,
                'context' => [
                    'user' => $user,
                    'conversation_id' => null,
                ],
            ]);

            // Process through the MultiAgentRouterNode
            $this->info('🔍 Analyzing intent...');
            $startTime = microtime(true);

            $node = new MultiAgentRouterNode;
            $result = $node->handle($event);

            $executionTime = round(microtime(true) - $startTime, 3);

            // Display results
            $this->newLine();
            $this->displayResults($result, $executionTime);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Display the workflow results
     */
    protected function displayResults($result, float $executionTime): void
    {
        // Tools used
        $toolsUsed = $result->get('tools_used', []);
        $this->info('🛠️  Tools Used: '.implode(', ', $toolsUsed));
        $this->newLine();

        // Get formatted result
        $formattedResult = $result->get('formatted_result', []);

        // Display based on result type
        if (isset($formattedResult['type'])) {
            match ($formattedResult['type']) {
                'conversation' => $this->displayConversationResult($formattedResult),
                'expense' => $this->displayExpenseResult($formattedResult),
                'database' => $this->displayDatabaseResult($formattedResult),
                'search' => $this->displaySearchResult($formattedResult),
                'combined' => $this->displayCombinedResult($formattedResult),
                'error' => $this->displayErrorResult($formattedResult),
                default => $this->displayGenericResult($formattedResult),
            };
        } else {
            $this->displayGenericResult($formattedResult);
        }

        // Metadata
        $this->newLine();
        $this->comment("⏱️  Execution time: {$executionTime}s");

        $metadata = $result->get('metadata', []);
        if (isset($metadata['tokens_used'])) {
            $this->comment("🎫 Tokens used: {$metadata['tokens_used']}");
        }
    }

    /**
     * Display conversation result
     */
    protected function displayConversationResult(array $result): void
    {
        $this->line('💬 <fg=cyan>AI Response:</>');
        $this->line('   '.$result['response']);
    }

    /**
     * Display expense result
     */
    protected function displayExpenseResult(array $result): void
    {
        $this->line('💰 <fg=green>Expense:</>');

        // Check if there's an error
        if (isset($result['error'])) {
            $this->error('❌ Error: '.$result['error']);

            return;
        }

        // Display message if available
        if (isset($result['message'])) {
            $this->line('   '.$result['message']);
        }

        // Display expense details
        if (isset($result['expense'])) {
            $expense = $result['expense'];
            if (isset($expense['amount'])) {
                $this->line('   Amount: $'.number_format($expense['amount'], 2));
            }
            if (isset($expense['description'])) {
                $this->line('   Description: '.$expense['description']);
            }
            if (isset($expense['category'])) {
                $this->line('   Category: '.$expense['category']);
            }
            if (isset($expense['date'])) {
                $this->line('   Date: '.$expense['date']);
            }
        }

        // For list actions, display expenses
        if (isset($result['expenses']) && is_array($result['expenses'])) {
            $this->newLine();
            $this->line('   <fg=cyan>Recent Expenses:</>');
            foreach (array_slice($result['expenses'], 0, 5) as $expense) {
                $this->line(sprintf(
                    '   • $%.2f - %s (%s)',
                    $expense['amount'] ?? 0,
                    $expense['description'] ?? 'N/A',
                    $expense['date'] ?? 'N/A'
                ));
            }
        }
    }

    /**
     * Display database result
     */
    protected function displayDatabaseResult(array $result): void
    {
        $this->line('🗄️  <fg=blue>Database Query:</>');

        // Check for error first
        if (isset($result['error'])) {
            $this->error('❌ Error: '.$result['error']);

            return;
        }

        // Display message or response
        if (isset($result['response'])) {
            $this->line('   '.$result['response']);
        } elseif (isset($result['result']['message'])) {
            $this->line('   '.$result['result']['message']);
        }

        // Display record count
        if (isset($result['result']['count'])) {
            $this->line('   Records found: '.$result['result']['count']);
        } elseif (isset($result['records_affected'])) {
            $this->line('   Records affected: '.$result['records_affected']);
        }

        // Display data if available
        if (isset($result['result']['data']) && is_array($result['result']['data']) && count($result['result']['data']) > 0) {
            $this->newLine();
            $this->line('   <fg=cyan>Results:</>');
            foreach (array_slice($result['result']['data'], 0, 5) as $i => $record) {
                $this->line('   '.($i + 1).'. '.json_encode($record));
            }
        }
    }

    /**
     * Display search result
     */
    protected function displaySearchResult(array $result): void
    {
        $this->line('🔍 <fg=yellow>Search Results:</>');

        // Check for error first
        if (isset($result['error'])) {
            $this->error('❌ Error: '.$result['error']);

            return;
        }

        // Display message if available
        if (isset($result['message'])) {
            $this->line('   '.$result['message']);
        }

        // Display search results
        if (isset($result['results']) && is_array($result['results']) && count($result['results']) > 0) {
            $this->newLine();
            $this->line('   <fg=cyan>Top Results:</>');
            foreach (array_slice($result['results'], 0, 5) as $i => $item) {
                $this->line('   '.($i + 1).'. '.$item['title']);
                if (isset($item['link'])) {
                    $this->line('      '.$item['link']);
                }
                if (isset($item['snippet'])) {
                    $snippet = substr($item['snippet'], 0, 100).'...';
                    $this->line('      '.$snippet);
                }
                $this->newLine();
            }
        }

        // Display query info
        if (isset($result['query'])) {
            $this->comment('   Query: '.$result['query']);
        }
        if (isset($result['count'])) {
            $this->comment('   Total results: '.$result['count']);
        }
    }

    /**
     * Display combined result
     */
    protected function displayCombinedResult(array $result): void
    {
        $this->line('🔄 <fg=magenta>Combined Results:</>');
        $this->line('   '.$result['summary']);
    }

    /**
     * Display error result
     */
    protected function displayErrorResult(array $result): void
    {
        $this->error('❌ '.$result['response']);
    }

    /**
     * Display generic result
     */
    protected function displayGenericResult(array $result): void
    {
        $this->line('📋 <fg=white>Response:</>');

        if (isset($result['response'])) {
            $this->line('   '.$result['response']);
        } elseif (isset($result['message'])) {
            $this->line('   '.$result['message']);
        } else {
            $this->line('   '.json_encode($result, JSON_PRETTY_PRINT));
        }
    }
}
