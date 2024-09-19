#!/usr/bin/env python2

from dnslib import DNSRecord, DNSHeader, RR, QTYPE, A
import SocketServer

class DNSHandler(SocketServer.BaseRequestHandler):
    def handle(self):
        data, socket = self.request
        request = DNSRecord.parse(data)
        reply = DNSRecord(DNSHeader(id=request.header.id, qr=1, aa=1, ra=1), q=request.q)
        qname = request.q.qname
        qtype = QTYPE[request.q.qtype]

        # Log the query
        print("Received query for {} ({})".format(qname, qtype))

        # Respond with 127.0.0.1 for all A record queries
        if qtype == 'A':
            reply.add_answer(RR(qname, QTYPE.A, rdata=A("127.0.0.1"), ttl=60))

        # Send the reply
        socket.sendto(reply.pack(), self.client_address)

if __name__ == "__main__":
    server = SocketServer.UDPServer(("0.0.0.0", 53), DNSHandler)
    print("DNS server is running on port 53...")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nServer is shutting down.")
        server.shutdown()
