import { reactive } from "vue"

export const snackbarState = reactive({
  visible: false,
  message: "",
  color: "info",
  timeout: 3000,
})

export const useSnackbar = () => ({
  showSnackbar({ message, color = "info", timeout = 3000 }) {
    snackbarState.message = message
    snackbarState.color = color
    snackbarState.timeout = timeout
    snackbarState.visible = true
  },
  snackbarState,
})
