import { useQueryClient } from "@tanstack/vue-query"

export const invalidateQueries = queryKey => {
  const queryClient = useQueryClient()
    
  return queryClient.invalidateQueries({
    queryKey: Array.isArray(queryKey) ? queryKey : [queryKey],
  })
} 
