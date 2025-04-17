import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query'

export default function (app) {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        refetchOnWindowFocus: false,
        retry: 1,
        staleTime: 5 * 60 * 1000, // 5 minutes
      },
    },
  })
  
  app.use(VueQueryPlugin, {
    queryClient,
  })
} 
