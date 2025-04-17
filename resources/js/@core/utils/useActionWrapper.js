import { useSnackbar } from "./useSnackbar"


export function useActionWrapper(){
  const { showSnackbar } = useSnackbar()

  return async (action, { successMessage } = {})=>{
    try {
      const result =  await action()
      if(successMessage) {
        showSnackbar({
          message: successMessage,
        })
      }
      
      return result
    } catch (err){
      console.log('error show snackbar')
      showSnackbar({
        message: err.name,
        color: 'red',
      })
    }
  } 

}
