<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

interface AuditLog
{
    public function record(string $entry): void;
}

final class InMemoryAuditLog implements AuditLog
{
    /** @var list<string> */
    public array $entries = [];

    public function record(string $entry): void
    {
        $this->entries[] = $entry;
    }
}

// A mix of a scalar override, a defaulted scalar, and an autowired dependency.
final class ReportJob
{
    public function __construct(
        private string $name,
        private AuditLog $audit,
        private int $limit = 10,
        private bool $dryRun = false,
    ) {
    }

    public function run(): string
    {
        $this->audit->record($this->name);

        return sprintf('%s (limit=%d, dryRun=%s)', $this->name, $this->limit, $this->dryRun ? 'yes' : 'no');
    }
}

$container = app();
$container->set(AuditLog::class, static fn (): AuditLog => new InMemoryAuditLog());

// make() resolves parameters in order: named override, then the container,
// then the constructor default. Only $name has to be supplied.
printf("override + default : %s\n", $container->make(ReportJob::class, ['name' => 'sales'])->run());
printf("all supplied       : %s\n", $container->make(ReportJob::class, [
    'name' => 'inventory',
    'limit' => 5,
    'dryRun' => true,
])->run());

// The global app() helper does the same thing.
printf("via app()          : %s\n", app(ReportJob::class, ['name' => 'nightly'])->run());

$audit = $container->get(AuditLog::class);
printf("audited runs       : %s\n", implode(', ', $audit->entries));

// A parameter the container cannot satisfy is reported clearly.
try {
    $container->make(ReportJob::class);
} catch (\Strukt\Container\ContainerException $exception) {
    printf("missing override   : %s\n", $exception->getMessage());
}
