# Development Server Coordination

- Before starting a development server, check whether the user already has the
	application running. If a user-run instance is available, reuse it for live
	verification instead of starting another process or choosing alternate ports.
- Treat development servers started by the user as user-owned. Do not stop,
	restart, reload, rebuild through, or otherwise disrupt them without first
	telling the user why the lifecycle action is needed and receiving explicit
	confirmation.
- This confirmation requirement also applies when a task appears to require a
	server restart after configuration changes, database seeding, cache clearing,
	or any other process that could interrupt the user's active workflow.
- When an existing server cannot be reused, report the reason and ask for
	confirmation before starting a separate instance.
