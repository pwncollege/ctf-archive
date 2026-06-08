'use server'

export async function submitAction(data: { message: string }) {
  
  return `Server received: ${data.message}`
}

