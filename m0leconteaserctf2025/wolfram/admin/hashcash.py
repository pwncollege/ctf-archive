import hashlib
from datetime import datetime
from uuid import uuid4 as uuid

zeros_required = 26


def request_payment():
    resource = uuid().hex
    date = datetime.today().strftime('%Y%m%d')
    payment = input(f"hashcash -mqb26 \"{resource}\"\n> ").strip()
    payment_pieces = payment.split(":")
    if (
        len(payment_pieces) != 7 or
        not payment_pieces[0].isdigit() or int(payment_pieces[0]) != 1 or
        not payment_pieces[1].isdigit() or int(payment_pieces[1]) < zeros_required or
        len(payment_pieces[2]) != 6 or not payment_pieces[2].isdigit() or abs(int(payment_pieces[2]) - int(datetime.today().strftime('%y%m%d'))) > 2 or
        payment_pieces[3] != resource or
        payment_pieces[4] != "" or
        int.from_bytes(hashlib.sha1(payment.encode("ascii")).digest(), "big") >> (hashlib.sha1().digest_size * 8 - zeros_required)
    ):
        return False
    return True
