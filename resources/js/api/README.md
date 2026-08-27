# API

Here lies API wrappers for Heymo. Use Vue composable style to declare and call API endpoints.
One file per collection. Sample design:

```ts
export function useDashboardApi() {
  // use ofetch as adapter
  // GET /dashboard
  function getDashboard() {
    return api.get("/dashboard");
  }
  return {
    getDashboard,
  };
}
```
