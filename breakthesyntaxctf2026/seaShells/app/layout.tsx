import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: 'Shells are the best!',
  description: 'Join our team!',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <body style={{ margin: 0, padding: 0, fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' }}>{children}</body>
    </html>
  )
}

