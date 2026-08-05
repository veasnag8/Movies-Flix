import api from '../api/client'

const DEFAULT_INTERVAL_MS = 10 * 60 * 1000

let timer = null
let inFlight = false

async function pingOnce() {
  if (inFlight) return
  inFlight = true

  try {
    await api.get('/up', { timeout: 10000 })
  } catch {
    // Keep-alive pings should fail silently so the app never feels broken.
  } finally {
    inFlight = false
  }
}

export function startKeepAlive(intervalMs = DEFAULT_INTERVAL_MS) {
  if (typeof window === 'undefined' || timer) return

  const shouldPing = () => document.visibilityState === 'visible' || !document.hidden

  const schedule = () => {
    clearInterval(timer)
    timer = setInterval(() => {
      if (shouldPing()) {
        pingOnce()
      }
    }, intervalMs)
  }

  document.addEventListener('visibilitychange', () => {
    if (shouldPing()) {
      pingOnce()
    }
  })

  window.addEventListener('focus', pingOnce)

  if (shouldPing()) {
    pingOnce()
  }

  schedule()
}

